<?php
declare(strict_types=1);

/*
 * Mise en forme simple et sûre pour les rédacteurs (aucun HTML accepté) :
 *   ## Titre          ### Sous-titre
 *   - élément de liste
 *   **gras**  *italique*  [texte du lien](https://exemple.org)
 *   Une ligne vide sépare les paragraphes.
 */

function markup(?string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", trim((string) $text));
    if ($text === '') {
        return '';
    }
    $html = '';
    $paragraph = [];
    $list = [];

    $flushParagraph = function () use (&$paragraph, &$html) {
        if ($paragraph) {
            $html .= '<p>' . implode('<br>', array_map('markup_inline', $paragraph)) . "</p>\n";
            $paragraph = [];
        }
    };
    $flushList = function () use (&$list, &$html) {
        if ($list) {
            $html .= "<ul>\n" . implode('', array_map(fn($li) => '<li>' . markup_inline($li) . "</li>\n", $list)) . "</ul>\n";
            $list = [];
        }
    };

    foreach (explode("\n", $text) as $line) {
        $trim = trim($line);
        if ($trim === '') {
            $flushParagraph();
            $flushList();
        } elseif (preg_match('/^(#{2,3})\s+(.+)$/', $trim, $m)) {
            $flushParagraph();
            $flushList();
            $tag = strlen($m[1]) === 2 ? 'h2' : 'h3';
            $html .= "<$tag>" . markup_inline($m[2]) . "</$tag>\n";
        } elseif (preg_match('/^[-•*]\s+(.+)$/u', $trim, $m)) {
            $flushParagraph();
            $list[] = $m[1];
        } else {
            $flushList();
            $paragraph[] = $trim;
        }
    }
    $flushParagraph();
    $flushList();
    return $html;
}

function markup_inline(string $text): string
{
    $text = e($text);
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) {
        $href = safe_link(html_entity_decode($m[2], ENT_QUOTES, 'UTF-8'));
        if ($href === '') {
            return $m[1];
        }
        $external = preg_match('#^https?://#', $href) ? ' target="_blank" rel="noopener noreferrer"' : '';
        return '<a href="' . e($href) . '"' . $external . '>' . $m[1] . '</a>';
    }, $text) ?? $text;
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text) ?? $text;
    $text = preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/', '<em>$1</em>', $text) ?? $text;
    return $text;
}

function strip_markup(string $text): string
{
    $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text) ?? $text;
    $text = preg_replace('/^#{2,3}\s+|^[-•]\s+/mu', '', $text) ?? $text;
    return str_replace(['**', '*'], '', $text);
}
