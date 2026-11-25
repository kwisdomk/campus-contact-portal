# Test checklist

| Test | Expected | Result |
|---|---|---|
| Import SQL in phpMyAdmin | 3 tables created with categories and sample rows | [ ] |
| Register a new user | Account saved with hashed password | [ ] |
| Log in / log out | Protected pages redirect to login when logged out | [ ] |
| Update profile | Updated username/email saved in MySQL | [ ] |
| Create a contact message | Message saved in `messages` table | [ ] |
| Filter and sort messages | Search and sort return matching rows | [ ] |
| Add 6+ messages | Pagination shows at least 2 pages | [ ] |
| Edit and delete message | Changes persist in MySQL | [ ] |
| CSV export and import | Valid rows imported, invalid rows skipped | [ ] |
| Open info page | `last_login` cookie date and category counts display | [ ] |
| Test XSS input | Script tags render as plain text | [ ] |
| Test invalid input | Server validation blocks saving and shows errors | [ ] |
| Test cross-user access | Users cannot edit or delete another user's messages | [ ] |
| Inspect HTML metadata | Page titles, meta description, and Open Graph tags present | [ ] |
| Check mobile layout | Navigation and tables stay readable on small screens | [ ] |

## Defense notes

Topics to review: PDO prepared statements, HTML escaping (`htmlspecialchars`), GET vs POST, cookies vs sessions, password hashing, CSRF tokens, the 3-table ERD, `JOIN`/`GROUP BY` queries, and CSV handling.

## Submission items

- [ ] Source code and database SQL archive
- [ ] Report exported as PDF (ERD, architecture, screenshots, setup guide, comparisons, SEO checklist)
- [ ] All group members' full names and admission numbers listed
- [ ] Individual PDF uploads completed
