# Locket

Social 'read later' system for developers. Accessible via web, API, and MCP.

- [x] Need usernames, not real names.
- [ ] Multiple MCP servers - what should go in each? Not sure this makes sense, but want to show the ability
- [ ] Don't use ids for links, use short strings/slugs, less likely for AI agents to mess them up

# 'Now' page

kind of like a 'social feed' we can show publicly on the web and via an MCP tool, and via an API, but to update your public now status you have to be authed.

"Recent Statuses: Learning Rust for the first time, reading about the action pattern, etc..".

Make sure people know this is public, you can set your 'now status' on the web/MCP/API.

### Now actions

These will be accessible via web/api/mcp:

- `update_status(status)`
- `get_recent_statuses(count: 10)`

# Link Management System

Shared Links: One canonical link entry with metadata (title, description, auto-categorized)
Personal Instances: Each user gets their own relationship to that link with:

- Reading status: `unread`, `reading`, `read`, `reference`, `archived` (string backed Enum)
- Personal notes (HasMany)
- Date added to their list (created_at)

Categories (Auto-suggested)

- Read - Articles, tutorials, blog posts (status: unread → reading → read)
- Reference - Docs, cheat sheets, specs (status: saved, doesn't need "reading")
- Watch - Videos, courses, demos
- Tools - Libraries, utilities, SaaS discoveries

# MCP Implementation

## Tools

### Public Tools (No Auth)

- authenticate(username) (or email) - Register or login, just do email validation code? https://www.youtube.com/watch?v=6_Rz7c2BLp8
- register(username, email) - Register new account, no password because that feels insecure? But it's a demo? Just like sending it via a form? Can always forgot password if needed.
    - How do we add the new tools at this point though since we don't do list changed?
    - Maybe we add list changed for tools just on this call somehow? We can register the user, then say "here are your new tools!"
- get_recent_statuses() - Recently updated statuses, the 'social feed'
- get_trending_links() - Hot links this week
- get_recent_links() - Latest additions
- browse_category(category) - Browse by type
- search_links(query) - Search all public links

### Authenticated Tools

- ?logout()?
- update_status(message) - Share what you're up to
- add_link(url, category_hint) - Add new link or add existing to your list
- add_note(link_id, note) - Attach thoughts
- update_link_status(link_id, status) - Mark progress
- get_my_queue(status) - Your unread/reading items
- suggest_next_link() - AI picks something from your queue
- search_my_notes(query) - Search your notes
- query(query) - Find your links or notes that match your query

## Resources

- /public/trending - Trending links data
- /public/feed - Public "now reading" activity
- /user/queue - Your reading queue

### Resource Templates

- /public/categories/{category} - Links by category
- /user/notes/{link_id} - Your notes on specific links

## Prompts

- `begin_consumption` - "You're about to help the user read [LINK]. Any following messages might be notes to save."
- `summarize_link` - Generate summary from link content
- `suggest_related` - Find similar content in their queue
- `weekly_digest` - Create reading recommendations

# Demo Flow

## Public Demo (Web + MCP)

1. Browse trending developer links
2. See recent additions by category
3. View public "now reading" social feed
4. Search all public content

## Authenticated Demo

1. Add a link (auto-categorizes as "Read")
2. Someone else adds same link to their list
3. Update reading status, add notes
4. Use "suggest next read" tool
5. Share current reading status publicly
6. Search through personal reading notes

AI Integration Features
Context-aware notes: When user says something after "begin reading", save as note
Smart categorization: Auto-suggest category when adding links
Personalized recommendations: Based on reading history and notes
Reading insights: Track what you've learned, themes in your reading

Simple MVP Scope

Link submission and categorization
Personal reading queues with status
Public trending/recent feeds
Basic note-taking on links
"Now reading" social feature

# Public web

- Latest statuses (nows)
- Recently added links
- Most popular links

---

- [ ] Session ID based tools? Within a session within Cursor should you be able to retain information?
- [ ] MCP tool testing - revisit once laravel/mcp has better testing support
