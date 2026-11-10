<?php

namespace Tests\Feature\Documentation;

use Tests\TestCase;

class MermaidDiagramsTest extends TestCase
{
    public function test_all_eight_diagrams_open_and_close_a_mermaid_code_block(): void
    {
        $paths = glob(base_path('docs/modelo-datos/0*.md'));
        $this->assertCount(8, $paths);

        foreach ($paths as $path) {
            $text = file_get_contents($path);
            $this->assertIsString($text);
            $this->assertSame(1, substr_count($text, '```mermaid'), $path);
            $this->assertSame(2, preg_match_all('/^```(?:mermaid)?\s*$/m', $text), $path);
            $this->assertMatchesRegularExpression(
                '/^```mermaid\r?\n[\s\S]*?^```\r?$/m',
                $text,
                $path
            );
            $this->assertStringNotContainsString("\n``\n", $text, $path);
        }
    }

    public function test_general_diagram_has_seven_colored_modules(): void
    {
        $text = file_get_contents(base_path('docs/modelo-datos/00_MAPA_GENERAL_43_TABLAS.md'));
        $this->assertIsString($text);
        $this->assertSame(7, preg_match_all('/^\s*subgraph\s+\w+\[/m', $text));
        $this->assertSame(7, preg_match_all('/^\s*style\s+\w+\s+fill:#/m', $text));
        $this->assertSame(44, preg_match_all('/^ {4}[\w]+\["[\w]+"\]$/m', $text));
    }
}
