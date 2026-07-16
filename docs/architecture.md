# Architecture

## Model

```
Team ─┬─ Role ── Ability (M2M)
      └─ ModelRole (morph user + role + team)
```

Ability checks resolve through `gr_model_roles` → `role` → `abilities`.

Team-scoped `can($user, $ability, $team)` accepts:

1. Assignments for that team
2. Global assignments (`team_id` null) as org-wide grants

## Impersonation

`start` writes an audit row + session keys (`guardrail.impersonator_id`, log id).  
`stop` stamps `ended_at` and clears session. Auth switching is intentionally left to the host app so GuardRail stays framework-light.

## Trade-offs

- Not a Spatie replacement—smaller surface for SaaS teams/impersonation
- Ability auto-create on sync-by-slug keeps demos ergonomic; production apps should create abilities explicitly
- Unique `(team_id, slug)` on roles; SQLite null uniqueness quirks documented for multi-global roles
