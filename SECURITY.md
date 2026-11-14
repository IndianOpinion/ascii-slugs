# Security policy

## Supported versions

| Version | Supported |
|---|---|
| 0.1.x | Yes |

## Reporting a vulnerability

Email hello@indianopinion.org with the details. Please do not open a public
issue for a security problem.

Include what you found, how to reproduce it, and the impact you expect. We
will acknowledge your report within 3 working days and aim to send a first
assessment within 10 working days. We will tell you when a fix is released
and credit you in the release notes if you wish.

## Scope

ascii-slugs builds URL slugs. Its output contains only `[a-z0-9-]`. The hash
fallback is not a secret and is not a security control; it must not be used
for access checks. The WP-CLI commands need shell access to the host and
have no web-facing trigger.
