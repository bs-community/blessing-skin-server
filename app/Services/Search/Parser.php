<?php

namespace App\Services\Search;

/**
 * Recursive-descent parser over the token stream produced by the Lexer.
 *
 * Grammar (precedence lowest to highest):
 *
 *   expr       := orExpr
 *   orExpr     := andExpr ( OR andExpr )*
 *   andExpr    := unary ( AND? unary )*        -- adjacency implies AND
 *   unary      := NOT unary | primary
 *   primary    := "(" expr ")" | constraint | term
 *   constraint := TERM ( ASSIGN | COMPARATOR ) value
 *               | TERM IN "(" value ( "," value )* ")"
 *
 * The parser never throws on malformed input: a search box should degrade to
 * a free-text search rather than 500 on a stray bracket.
 */
class Parser
{
    /** @var array<int, array{type: string, value: string}> */
    private array $tokens = [];

    private int $position = 0;

    public function parse(string $input): ?array
    {
        $this->tokens = (new Lexer())->tokenize($input);
        $this->position = 0;

        $node = $this->parseOr();

        // Trailing junk (an unmatched ")") is ignored rather than fatal.
        return $node;
    }

    private function parseOr(): ?array
    {
        $nodes = array_filter([$this->parseAnd()]);

        while ($this->current()['type'] === Lexer::T_OR) {
            $this->advance();
            $right = $this->parseAnd();
            if ($right !== null) {
                $nodes[] = $right;
            }
        }

        return $this->collapse('or', array_values($nodes));
    }

    private function parseAnd(): ?array
    {
        $nodes = [];

        while (true) {
            $type = $this->current()['type'];

            if (in_array($type, [Lexer::T_EOF, Lexer::T_RPAREN, Lexer::T_OR], true)) {
                break;
            }

            if ($type === Lexer::T_AND) {
                $this->advance();
                continue;
            }

            $node = $this->parseUnary();

            if ($node === null) {
                break;
            }

            $nodes[] = $node;
        }

        return $this->collapse('and', $nodes);
    }

    private function parseUnary(): ?array
    {
        if ($this->current()['type'] === Lexer::T_NOT) {
            $this->advance();
            $node = $this->parseUnary();

            return $node === null ? null : ['type' => 'not', 'node' => $node];
        }

        return $this->parsePrimary();
    }

    private function parsePrimary(): ?array
    {
        $token = $this->current();

        if ($token['type'] === Lexer::T_LPAREN) {
            $this->advance();
            $node = $this->parseOr();

            if ($this->current()['type'] === Lexer::T_RPAREN) {
                $this->advance();
            }

            return $node;
        }

        // Only an unquoted term can name a column; "foo":1 is free text.
        if ($token['type'] === Lexer::T_TERM) {
            $next = $this->peek();

            if ($next['type'] === Lexer::T_ASSIGN || $next['type'] === Lexer::T_COMPARATOR) {
                $this->advance();
                $operator = $this->current()['value'];
                $this->advance();

                return [
                    'type' => 'constraint',
                    'field' => $token['value'],
                    'operator' => $operator === ':' || $operator === '=' ? '=' : $operator,
                    'value' => $this->parseValue(),
                ];
            }

            if ($next['type'] === Lexer::T_IN) {
                return $this->parseIn($token['value']);
            }
        }

        if (in_array($token['type'], [Lexer::T_TERM, Lexer::T_STRING, Lexer::T_NULL], true)) {
            $this->advance();

            return ['type' => 'term', 'value' => $token['value']];
        }

        // Stray operator: consume it so the loop cannot spin forever.
        if ($token['type'] !== Lexer::T_EOF) {
            $this->advance();
        }

        return null;
    }

    private function parseIn(string $field): array
    {
        $this->advance(); // field
        $this->advance(); // in

        $values = [];

        if ($this->current()['type'] === Lexer::T_LPAREN) {
            $this->advance();

            while (!in_array($this->current()['type'], [Lexer::T_RPAREN, Lexer::T_EOF], true)) {
                if ($this->current()['type'] === Lexer::T_COMMA) {
                    $this->advance();
                    continue;
                }

                $values[] = $this->parseValue();
            }

            if ($this->current()['type'] === Lexer::T_RPAREN) {
                $this->advance();
            }
        } else {
            $values[] = $this->parseValue();
        }

        return ['type' => 'in', 'field' => $field, 'values' => $values];
    }

    /** Returns null for the NULL keyword so the applier can emit IS NULL. */
    private function parseValue(): ?string
    {
        $token = $this->current();

        if ($token['type'] === Lexer::T_NULL) {
            $this->advance();

            return null;
        }

        if ($token['type'] === Lexer::T_EOF) {
            return '';
        }

        $this->advance();

        return $token['value'];
    }

    private function collapse(string $type, array $nodes): ?array
    {
        return match (count($nodes)) {
            0 => null,
            1 => $nodes[0],
            default => ['type' => $type, 'nodes' => $nodes],
        };
    }

    private function current(): array
    {
        return $this->tokens[$this->position] ?? ['type' => Lexer::T_EOF, 'value' => ''];
    }

    private function peek(): array
    {
        return $this->tokens[$this->position + 1] ?? ['type' => Lexer::T_EOF, 'value' => ''];
    }

    private function advance(): void
    {
        $this->position++;
    }
}
