# Architecture: commonmark

## Purpose

A PHP implementation of the CommonMark Markdown specification, with support for GitHub-Flavored Markdown and a rich extension system. It converts Markdown text to HTML (or XML) via a pluggable parse–render pipeline.

## Directory Structure

```
src/
  Markdown_Converter.php                — Top-level API: convert(string) → RenderedContent
  Github_Flavored_Markdown_Converter.php — Pre-configured GFM converter
  Common_Mark_Converter.php             — Pre-configured CommonMark 1.0 converter

  Parser/
    Markdown_Parser.php                 — Orchestrates block parsing then inline parsing
    Cursor.php                          — Character-level input cursor (tracks position, indent, etc.)
    Block/                              — Block-level parsers (paragraphs, headings, lists, etc.)
    Inline/                             — Inline parsers (emphasis, links, code spans, etc.)

  Node/
    Node.php                            — AST base node (parent/child/sibling tree)
    Block/                              — Block AST nodes (Document, Paragraph, etc.)
    Inline/                             — Inline AST nodes (Text, Newline, etc.)
    Query.php                           — Fluent DSL for selecting AST nodes

  Renderer/
    Html_Renderer.php                   — Traverses AST and renders nodes to HTML
    Node_Renderer_Interface.php         — Contract for per-node renderers
    Block/ / Inline/                    — Built-in node renderers

  Reference/
    Reference_Map.php                   — Stores link reference definitions (e.g. [foo]: /url)
    Memory_Limited_Reference_Map.php    — Capped version to prevent DoS via large inputs

  Extension/
    TableOfContents/                    — TOC generation extension
    TaskList/                           — GFM task list checkboxes

  Normalizer/
    Slug_Normalizer.php                 — Converts heading text to URL-safe slug (for TOC anchors)
    Unique_Slug_Normalizer.php          — Deduplicates slugs within a single document

  Delimiter/                            — Delimiter processing for emphasis/strong (run-of-asterisks logic)
  Util/                                 — HTML encoding, URL encoding, entity decoding, regex helpers
  Xml/                                  — XML output renderer and converter
```

## Key Design Decisions

- **Two-pass parsing** — the parser first resolves block structure (headings, lists, code blocks), then processes inlines (emphasis, links) within each block. This matches the CommonMark spec's two-phase model.
- **Extension system** — `MarkdownConverter` accepts an `Environment` that registers custom block/inline parsers, node renderers, delimiter processors, and event listeners. All built-in features are implemented as extensions.
- **Immutable AST** — the AST is built during parsing and consumed during rendering; renderers do not mutate nodes.
- **Memory-limited reference map** — link reference definitions are capped to prevent denial-of-service attacks via pathologically large inputs.
- **CommonMark spec compliance** — the test suite runs against the official CommonMark spec fixture files.

## Extension Points

- Register a custom `Block_Start_Parser_Interface` to handle new block-level constructs.
- Register a custom `Inline_Parser_Interface` for new inline syntax.
- Register a custom `Node_Renderer_Interface` for custom AST nodes.
- Register a custom `Delimiter_Processor_Interface` for new emphasis-like delimiters.

## Dependency Flow

```
MarkdownConverter::convert(markdown)
  ├── MarkdownParser::parse(input)         → Document AST
  │     ├── Block parsers (per line)
  │     └── Inline parsers (per block)
  └── HtmlRenderer::renderDocument(AST)   → RenderedContent (HTML string)
        └── NodeRenderer per AST node type
```
