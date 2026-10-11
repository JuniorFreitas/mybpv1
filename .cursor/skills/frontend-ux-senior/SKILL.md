---
name: frontend-ux-senior
description: Frontend senior, UX senior, UI review, accessibility, responsive design, design systems, interaction design. Use when implementing or reviewing user interfaces, frontend flows, visual polish, usability, or product experience.
---

# Frontend UX Senior

Use this skill for frontend implementation, UX critique, UI reviews, accessibility improvements, responsive design, visual refinement, design system decisions, interaction design, and product experience work.

## Operating Principles

- Understand the product context and existing code before changing UI.
- Preserve the current design language unless the task explicitly asks for a redesign.
- Optimize for user task completion, clarity, accessibility, responsiveness, and maintainability.
- Prefer the smallest correct implementation that improves the experience.
- Avoid generic "template" UI. Make layout, typography, spacing, and interaction choices that fit the actual product.

## Frontend Checklist

- Use existing components, tokens, utilities, and styling conventions first.
- Keep semantic HTML and accessible names correct.
- Ensure keyboard navigation, focus states, and focus order work.
- Cover loading, empty, error, disabled, success, and long-content states where relevant.
- Validate mobile and desktop behavior for meaningful UI changes.
- Avoid unnecessary dependencies, broad refactors, and premature abstractions.
- Match the repository's framework conventions for state, routing, forms, data fetching, and tests.

## UX Checklist

- Clarify the primary user goal and the primary action on the screen.
- Strengthen visual hierarchy before adding more UI.
- Reduce cognitive load through clear grouping, labels, helper text, and predictable flows.
- Make feedback immediate and actionable for user actions and errors.
- Check contrast, tap targets, spacing rhythm, and readability.
- Prefer specific microcopy over vague labels.
- Ensure destructive or irreversible actions have appropriate friction.

## Review Output

When reviewing UI or frontend code:

- Lead with findings ordered by severity.
- Include file, component, or screen references when available.
- Explain the user impact, not only the technical issue.
- Separate confirmed issues from assumptions.
- Mention testing gaps, especially accessibility and responsive coverage.

## Implementation Output

When implementing:

- Inspect relevant files and existing UI patterns first.
- Make focused edits.
- Verify with the most relevant available checks: tests, typecheck, lint, build, or browser validation.
- Summarize changed files and verification results.
