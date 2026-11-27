from __future__ import annotations

import os

import httpx
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("undc-asistente-tools")


def _base_url() -> str:
    return os.getenv("LARAVEL_INTERNAL_BASE_URL", "http://127.0.0.1:8000").rstrip("/")


@mcp.tool()
async def listar_oficinas_catalogo() -> dict:
    """Lista las oficinas que tienen un catálogo público disponible en UNDC Market."""
    async with httpx.AsyncClient(base_url=_base_url(), timeout=12.0) as client:
        response = await client.get("/api/v1/tienda/catalogo/oficinas")
        response.raise_for_status()
        return response.json()


@mcp.tool()
async def consultar_catalogo_oficina(office_id: int) -> dict:
    """Consulta conceptos publicados de una oficina usando la API oficial de Catálogo. No crea pedidos ni procesa pagos."""
    if office_id < 1:
        raise ValueError("office_id debe ser un entero positivo")
    async with httpx.AsyncClient(base_url=_base_url(), timeout=12.0) as client:
        response = await client.get("/api/v1/tienda/catalogo", params={"office_id": office_id})
        response.raise_for_status()
        return response.json()


if __name__ == "__main__":
    mcp.run(transport="stdio")
