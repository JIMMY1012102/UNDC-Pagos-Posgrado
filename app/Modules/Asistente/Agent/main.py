from __future__ import annotations

import os
import secrets
from typing import Any

from fastapi import Depends, FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

from .service import AssistantAgent

app = FastAPI(title="UNDC Asistente Agent", version="1.0.0")
agent = AssistantAgent()


class MessageRequest(BaseModel):
    message: str = Field(min_length=1, max_length=4000)


class MessageResponse(BaseModel):
    answer: str


async def verify_internal_token(authorization: str | None = Header(default=None)) -> None:
    expected = os.getenv("ASSISTANT_AGENT_TOKEN", "").strip()
    supplied = authorization.removeprefix("Bearer ").strip() if authorization else ""
    if not expected or not secrets.compare_digest(supplied, expected):
        raise HTTPException(status_code=401, detail="Unauthorized")


@app.get("/health")
async def health() -> dict[str, str]:
    return {"status": "ok", "service": "asistente-agent"}


@app.post("/messages", response_model=MessageResponse, dependencies=[Depends(verify_internal_token)])
async def messages(payload: MessageRequest) -> MessageResponse:
    try:
        return MessageResponse(answer=await agent.reply(payload.message))
    except Exception as exc:
        # Never expose provider details, API keys, or internal URLs to Laravel.
        raise HTTPException(status_code=503, detail="Assistant temporarily unavailable") from exc
