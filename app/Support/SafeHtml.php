<?php

namespace App\Support;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;

final class SafeHtml
{
    /** @var list<string> */
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'code', 'div', 'em', 'h1', 'h2', 'h3', 'h4',
        'hr', 'i', 'img', 'li', 'ol', 'p', 'pre', 's', 'span', 'strong', 'table',
        'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    /** @var list<string> */
    private const DROP_CONTENT_TAGS = [
        'embed', 'iframe', 'noscript', 'object', 'script', 'style', 'template',
    ];

    /** @var array<string, list<string>> */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    public static function clean(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML(
            '<?xml encoding="UTF-8"><!DOCTYPE html><html><body>'.$html.'</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }

        self::sanitizeChildren($body);

        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    /**
     * Parse formatting like **bold**, *italic*, bullets, and convert newlines safely.
     * Prevents raw markdown characters from leaking to end users.
     */
    public static function formatHumanText(?string $text): string
    {
        if (! $text || trim($text) === '') {
            return '';
        }

        // 1. Convert markdown bold **text** to <strong>text</strong>
        $formatted = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);

        // 2. Convert markdown italic *text* or _text_ to <em>text</em>
        $formatted = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', (string) $formatted);

        // 3. Remove heading hashes (# Heading -> Heading)
        $formatted = preg_replace('/^#{1,6}\s+/m', '', (string) $formatted);

        // 4. Convert markdown bullets "- item" or "* item" into clean bullet characters
        $formatted = preg_replace('/^[\*\-]\s+/m', '• ', (string) $formatted);

        // 5. Convert newlines to <br>
        $formatted = nl2br((string) $formatted);

        // 6. Run through safe HTML cleaner
        return self::clean($formatted);
    }

    /**
     * Strips all markdown syntax completely for clean plain text (e.g. form inputs, tables).
     */
    public static function stripMarkdown(?string $text): string
    {
        if (! $text) {
            return '';
        }

        $clean = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);
        $clean = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '$1', (string) $clean);
        $clean = preg_replace('/^#{1,6}\s+/m', '', (string) $clean);
        $clean = preg_replace('/^[\*\-]\s+/m', '', (string) $clean);
        $clean = preg_replace('/`{1,3}(.*?)`{1,3}/s', '$1', (string) $clean);

        return trim((string) $clean);
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        /** @var list<DOMNode> $children */
        $children = iterator_to_array($parent->childNodes);
        foreach ($children as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                if ($child->nodeType === XML_COMMENT_NODE) {
                    $parent->removeChild($child);
                }

                continue;
            }

            /** @var DOMElement $element */
            $element = $child;
            $tag = strtolower($element->tagName);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                if (in_array($tag, self::DROP_CONTENT_TAGS, true)) {
                    $parent->removeChild($element);
                } else {
                    self::unwrap($parent, $element);
                }

                continue;
            }

            $allowedAttributes = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
            /** @var list<DOMAttr> $attributes */
            $attributes = iterator_to_array($element->attributes);
            foreach ($attributes as $attribute) {
                $name = strtolower($attribute->nodeName);
                if (! in_array($name, $allowedAttributes, true) || str_starts_with($name, 'on')) {
                    $element->removeAttributeNode($attribute);
                }
            }

            if ($tag === 'a' && $element->hasAttribute('href') && ! self::isSafeUrl($element->getAttribute('href'))) {
                $element->removeAttribute('href');
            }
            if ($tag === 'img' && $element->hasAttribute('src')) {
                $src = HostRelativeUrl::normalize($element->getAttribute('src')) ?? '';
                $element->setAttribute('src', $src);

                if (! self::isSafeUrl($src)) {
                    $element->removeAttribute('src');
                }
            }
            if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            }

            self::sanitizeChildren($element);
        }
    }

    private static function unwrap(DOMNode $parent, DOMElement $element): void
    {
        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }

    private static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https', 'mailto'], true);
    }
}
