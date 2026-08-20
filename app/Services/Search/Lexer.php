<?php

namespace App\Services\Search;

/**
 * Turns a raw search box string into a flat list of tokens.
 *
 * Quoted values keep their contents verbatim so a term such as
 * "hello world" stays one value instead of two free-text terms.
 */
class Lexer
{
    public const T_ASSIGN = 'assign';       // : or =
    public const T_COMPARATOR = 'comparator'; // > >= < <= !=
    public const T_AND = 'and';
    public const T_OR = 'or';
    public const T_NOT = 'not';
    public const T_IN = 'in';
    public const T_NULL = 'null';
    public const T_LPAREN = 'lparen';
    public const T_RPAREN = 'rparen';
    public const T_COMMA = 'comma';
    public const T_STRING = 'string';       // came from quotes, never a keyword
    public const T_TERM = 'term';
    public const T_EOF = 'eof';

    /** Characters that terminate a bare term. */
    private const BOUNDARY = " \t\n\r:=<>!\"'(),";

    /** @return array<int, array{type: string, value: string}> */
    public function tokenize(string $input): array
    {
        $tokens = [];
        $length = strlen($input);
        $i = 0;

        while ($i < $length) {
            $char = $input[$i];

            if (ctype_space($char)) {
                $i++;
                continue;
            }

            if ($char === '"' || $char === "'") {
                [$value, $i] = $this->readQuoted($input, $i, $char);
                $tokens[] = ['type' => self::T_STRING, 'value' => $value];
                continue;
            }

            if ($char === ':' || $char === '=') {
                $tokens[] = ['type' => self::T_ASSIGN, 'value' => $char];
                $i++;
                continue;
            }

            if ($char === '>' || $char === '<' || $char === '!') {
                $operator = $char;
                if ($i + 1 < $length && $input[$i + 1] === '=') {
                    $operator .= '=';
                    $i++;
                }
                $i++;

                // A bare "!" is only meaningful as "!=", otherwise treat it as negation.
                if ($operator === '!') {
                    $tokens[] = ['type' => self::T_NOT, 'value' => '!'];
                    continue;
                }

                $tokens[] = ['type' => self::T_COMPARATOR, 'value' => $operator];
                continue;
            }

            if ($char === '(') {
                $tokens[] = ['type' => self::T_LPAREN, 'value' => $char];
                $i++;
                continue;
            }

            if ($char === ')') {
                $tokens[] = ['type' => self::T_RPAREN, 'value' => $char];
                $i++;
                continue;
            }

            if ($char === ',') {
                $tokens[] = ['type' => self::T_COMMA, 'value' => $char];
                $i++;
                continue;
            }

            $start = $i;
            while ($i < $length && !str_contains(self::BOUNDARY, $input[$i])) {
                $i++;
            }

            // Guard against a boundary character we did not consume above.
            if ($i === $start) {
                $i++;
                continue;
            }

            $word = substr($input, $start, $i - $start);
            $tokens[] = ['type' => $this->keyword($word), 'value' => $word];
        }

        $tokens[] = ['type' => self::T_EOF, 'value' => ''];

        return $tokens;
    }

    private function keyword(string $word): string
    {
        return match (strtolower($word)) {
            'and' => self::T_AND,
            'or' => self::T_OR,
            'not' => self::T_NOT,
            'in' => self::T_IN,
            'null' => self::T_NULL,
            default => self::T_TERM,
        };
    }

    /** @return array{0: string, 1: int} */
    private function readQuoted(string $input, int $i, string $quote): array
    {
        $i++; // opening quote
        $start = $i;
        $length = strlen($input);

        while ($i < $length && $input[$i] !== $quote) {
            $i++;
        }

        $value = substr($input, $start, $i - $start);

        if ($i < $length) {
            $i++; // closing quote; an unterminated quote just runs to the end
        }

        return [$value, $i];
    }
}
