from __future__ import annotations

import os
import sys
from typing import Any

from google import genai
from google.genai import errors, types
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client


def _model_chain() -> list[str]:
    primary = os.getenv("GEMINI_MODEL", "").strip()
    backups = [item.strip() for item in os.getenv("GEMINI_FALLBACK_MODELS", "").split(",") if item.strip()]
    legacy = os.getenv("GEMINI_FALLBACK_MODEL", "").strip()
    return list(dict.fromkeys(item for item in [primary, *backups, legacy] if item))


def _function_declarations(mcp_tools: list[Any]) -> list[types.FunctionDeclaration]:
    declarations = []
    for tool in mcp_tools:
        schema = tool.inputSchema or {"type": "object", "properties": {}}
        declarations.append(types.FunctionDeclaration(
            name=tool.name,
            description=tool.description or tool.name,
            parameters_json_schema=schema,
        ))
    return declarations


class AssistantAgent:
    def __init__(self) -> None:
        self.api_key = os.getenv("GEMINI_API_KEY", "").strip()
        self.models = _model_chain()
        self.max_tool_rounds = int(os.getenv("ASSISTANT_MAX_TOOL_ROUNDS", "5"))

    async def reply(self, message: str) -> str:
        if not self.api_key or not self.models:
            raise RuntimeError("Gemini agent is not configured")

        async with stdio_client(StdioServerParameters(
            command=sys.executable,
            args=["-m", "app.Modules.Asistente.Agent.mcp_server"],
            env=os.environ.copy(),
        )) as (read_stream, write_stream):
            async with ClientSession(read_stream, write_stream) as session:
                await session.initialize()
                available = await session.list_tools()
                declarations = _function_declarations(available.tools)
                client = genai.Client(api_key=self.api_key)
                try:
                    return await self._generate_with_fallback(
                        client, session, message, declarations
                    )
                finally:
                    await client.aio.aclose()

    async def _generate_with_fallback(
        self, client: genai.Client, session: ClientSession, message: str,
        declarations: list[types.FunctionDeclaration],
    ) -> str:
        for model in self.models:
            try:
                return await self._run_model(client, session, model, message, declarations)
            except errors.APIError as exc:
                try:
                    status = int(getattr(exc, "code", 0))
                except (TypeError, ValueError):
                    status = 0
                if status not in (404, 408, 429) and status < 500:
                    raise
            except (TimeoutError, OSError, ConnectionError):
                # A transport failure is shared by all model IDs; do not repeat it.
                raise
        raise RuntimeError("No configured Gemini model could answer")

    async def _run_model(
        self, client: genai.Client, session: ClientSession, model: str,
        message: str, declarations: list[types.FunctionDeclaration],
    ) -> str:
        history: list[types.Content] = [types.Content(
            role="user", parts=[types.Part.from_text(text=message)]
        )]
        tools = [types.Tool(function_declarations=declarations)] if declarations else []
        for _ in range(self.max_tool_rounds + 1):
            response = await client.aio.models.generate_content(
                model=model,
                contents=history,
                config=types.GenerateContentConfig(
                    tools=tools,
                    temperature=0.2,
                    system_instruction=(
                        "Eres el asistente virtual de la Escuela de Posgrado de la UNDC. "
                        "Responde siempre en español, de forma directa, breve y natural. "
                        "Por defecto usa de 1 a 3 frases cortas o hasta 3 viñetas; responde "
                        "solo lo que preguntaron. No uses saludos, introducciones, conclusiones, "
                        "repeticiones ni explicaciones largas. Amplía únicamente si el usuario "
                        "pide detalles. "
                        "Cuando pregunten por oficinas, servicios, códigos, importes o requisitos, "
                        "consulta las herramientas MCP y basa la respuesta en sus datos. "
                        "Muestra importes con S/ y conserva exactamente el valor del catálogo; "
                        "llámalos importes de referencia cuando corresponda. No inventes precios, "
                        "disponibilidad, requisitos, códigos ni datos que no aparezcan. Si falta "
                        "un dato, dilo brevemente. Distingue siempre información del catálogo "
                        "de una operación de pago: no afirmes que creaste pedidos, modificaste "
                        "datos ni procesaste pagos, porque las herramientas son solo de consulta. "
                        "Si la pregunta es ambigua, haz una sola pregunta breve para aclararla."
                    ),
                ),
            )
            candidate = response.candidates[0] if response.candidates else None
            content = candidate.content if candidate else None
            if content is None:
                raise RuntimeError("Gemini returned no candidate")
            calls = [part.function_call for part in (content.parts or []) if part.function_call]
            if not calls:
                answer = response.text
                if answer and answer.strip():
                    return answer.strip()
                raise RuntimeError("Gemini returned an empty response")

            history.append(content)
            tool_parts = []
            for call in calls:
                result = await session.call_tool(call.name, arguments=dict(call.args or {}))
                payload = {
                    "isError": result.isError,
                    "content": [
                        {"type": item.type, "text": getattr(item, "text", "")}
                        for item in (result.content or [])
                    ],
                }
                tool_parts.append(types.Part.from_function_response(
                    name=call.name, response=payload
                ))
            history.append(types.Content(role="user", parts=tool_parts))
        raise RuntimeError("Assistant exceeded the maximum tool-call rounds")
