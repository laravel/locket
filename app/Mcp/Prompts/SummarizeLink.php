<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class SummarizeLink extends Prompt
{
    protected string $description = 'Generate a comprehensive AI prompt to analyze and summarize web content with actionable insights, thought-provoking questions, and suggestions for further exploration';

    public function handle(Request $request): Response
    {
        $validated = $request->validate(['url' => 'required|url'], ['url' => 'Must be provided to generate a valid prompt. Ask the user for this']);

        return Response::text(<<<PROMPT
Please read and analyze the content at this URL: {$validated['url']}

After reading the content, provide a comprehensive response with the following structure:

## 📋 Summary
Provide a concise 2-3 sentence summary of the main topic and key points.

## 🎯 Key Insights & Takeaways
List 3-5 of the most important insights, discoveries, or actionable points from the content. Focus on what's genuinely valuable or surprising.

## 🤔 Questions & Areas for Further Investigation
Identify 3-4 thought-provoking questions or areas that deserve deeper exploration, such as:
- Concepts that could be expanded upon
- Related topics worth investigating
- Practical applications or implications
- Potential challenges or counterarguments

## 💡 Next Steps & Related Topics
Suggest specific actions the reader could take or related areas they might want to explore, including:
- Practical applications they could try
- Related articles, books, or resources to investigate
- Skills or knowledge areas to develop further
- People or communities to connect with

**Interactive Follow-up**: After providing this analysis, ask the user which specific aspect they'd like to dive deeper into, and offer to help them explore that area further through additional research, examples, or practical guidance.

Remember to:
- Be succinct. This user is incredibly busy and is looking for the most knowledge possible for the least time possible.
- Be genuinely curious and insightful, not just descriptive
- Challenge the reader to think critically
- Provide specific, actionable suggestions
- Ask engaging follow-up questions to continue the conversation
- Focus on what makes this content uniquely valuable or interesting
PROMPT);
    }

    public function arguments(): array
    {
        return [new Argument('url', 'URL to summarize', true)];
    }
}
