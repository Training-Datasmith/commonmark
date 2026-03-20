<?php

declare(strict_types=1);

/**
 * Example: Converting Markdown to HTML with league/commonmark.
 */

use League\CommonMark\Common_Mark_Converter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\Common_Mark_Core_Extension;
use League\CommonMark\Extension\GithubFlavoredMarkdown\Github_Flavored_Markdown_Extension;
use League\CommonMark\Extension\TaskList\Task_List_Extension;
use League\CommonMark\Github_Flavored_Markdown_Converter;
use League\CommonMark\Markdown_Converter;

require_once __DIR__ . '/../vendor/autoload.php';

// --- 1. Simple CommonMark conversion ---
$converter = new Common_Mark_Converter();
$html = $converter->convert('# Hello, **World**!');
echo $html . PHP_EOL;
// <h1>Hello, <strong>World</strong>!</h1>

// --- 2. GitHub-Flavored Markdown (includes tables, strikethrough, task lists) ---
$gfm = new Github_Flavored_Markdown_Converter();
$markdown = <<<MD
## Features

- [x] CommonMark compliant
- [x] GitHub-Flavored Markdown
- [ ] Custom extensions

| Name  | Type   |
|-------|--------|
| Alice | Admin  |
| Bob   | User   |
MD;

echo $gfm->convert($markdown) . PHP_EOL;

// --- 3. Custom environment with specific extensions ---
$env = new Environment([
    'html_input' => 'strip',        // strip raw HTML for security
    'allow_unsafe_links' => false,  // block javascript: links
    'max_nesting_level' => 100,
]);
$env->addExtension(new Common_Mark_Core_Extension());
$env->addExtension(new Github_Flavored_Markdown_Extension());
$env->addExtension(new Task_List_Extension());

$safe_converter = new Markdown_Converter($env);
$output = $safe_converter->convert('Click [here](javascript:alert(1)) — this link is scrubbed.');
echo $output . PHP_EOL;
