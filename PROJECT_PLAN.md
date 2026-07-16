# GuardRail — Project Plan

## Name

**GuardRail** (`hekal/guardrail`)  
Alternatives: `accessforge`, `policydeck`

## Vision

SaaS-oriented access control for Laravel: team-scoped roles, ability checks, and audited impersonation with reason + TTL—without replacing Spatie’s full ecosystem, focused on the patterns SaaS apps actually need.

## v0.1 scope

- Teams, roles (global or team-scoped), abilities
- Assign roles to users within a team
- `can` / `cannot` checks (role → abilities)
- Impersonation start/stop with reason, TTL, audit log
- Middleware `guardrail.ability`
- Pest tests for team isolation of roles

## Out of v0.1

- Attribute-based access control (ABAC)
- Policy codegen
- UI / Filament resources
- Hard dependency on TenantForge (compose later)
