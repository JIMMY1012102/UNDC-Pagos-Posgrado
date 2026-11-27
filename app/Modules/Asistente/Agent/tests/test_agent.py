from __future__ import annotations

import asyncio
import os
import sys
import unittest
from unittest.mock import patch

from app.Modules.Asistente.Agent.service import _function_declarations, _model_chain
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client


class AgentConfigurationTests(unittest.TestCase):
    def test_model_chain_keeps_primary_and_fallback_order_without_duplicates(self) -> None:
        with patch.dict(os.environ, {
            "GEMINI_MODEL": "primary",
            "GEMINI_FALLBACK_MODELS": "backup-a, backup-b,primary",
            "GEMINI_FALLBACK_MODEL": "legacy",
        }, clear=True):
            self.assertEqual(
                ["primary", "backup-a", "backup-b", "legacy"],
                _model_chain(),
            )

    def test_mcp_tools_convert_to_gemini_function_declarations(self) -> None:
        class Tool:
            name = "listar_oficinas_catalogo"
            description = "Lista oficinas"
            inputSchema = {"type": "object", "properties": {}, "required": []}

        declarations = _function_declarations([Tool()])
        self.assertEqual(1, len(declarations))
        self.assertEqual("listar_oficinas_catalogo", declarations[0].name)
        self.assertEqual("Lista oficinas", declarations[0].description)


class McpServerContractTests(unittest.TestCase):
    def test_server_exposes_only_read_only_catalog_tools(self) -> None:
        async def discover() -> list[str]:
            params = StdioServerParameters(
                command=sys.executable,
                args=["-m", "app.Modules.Asistente.Agent.mcp_server"],
                env=os.environ.copy(),
            )
            async with stdio_client(params) as (read_stream, write_stream):
                async with ClientSession(read_stream, write_stream) as session:
                    await session.initialize()
                    result = await session.list_tools()
                    return [tool.name for tool in result.tools]

        names = asyncio.run(discover())
        self.assertEqual(
            ["consultar_catalogo_oficina", "listar_oficinas_catalogo"],
            sorted(names),
        )


if __name__ == "__main__":
    unittest.main()
