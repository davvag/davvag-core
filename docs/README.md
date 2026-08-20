# DAVVAG Documentation

This folder is the structured documentation set for the DAVVAG framework. It is designed for both humans and AI agents that need to understand the framework, create new tenants, build applications, add services, define workflows, and create database schemas.

Start here, then read the topic file that matches your task.

## Documentation Map

| File | Purpose |
| --- | --- |
| [01-framework-overview.md](01-framework-overview.md) | Runtime flow, root folders, major modules, and constants. |
| [02-tenant-setup.md](02-tenant-setup.md) | How to create tenant/domain folders such as `davvag-core/example.com`. |
| [03-application-development.md](03-application-development.md) | How to create and register DAVVAG applications. |
| [04-components-and-services.md](04-components-and-services.md) | Component descriptors, frontend components, service handlers, and API routes. |
| [05-database-schemas.md](05-database-schemas.md) | Schema-driven database setup with `SOSSData`, including field and relationship metadata. |
| [06-workflows.md](06-workflows.md) | `davvag-flow` workflow files, node types, and execution. |
| [07-plugins.md](07-plugins.md) | Global and tenant-local plugin usage and extension patterns. |
| [08-auth-sessions-permissions.md](08-auth-sessions-permissions.md) | Auth flow, sessions, cookies, user groups, and access checks. |
| [09-deployment.md](09-deployment.md) | Apache, cPanel, PHP, CORS, media, and database deployment notes. |
| [10-ai-agent-playbook.md](10-ai-agent-playbook.md) | Step-by-step instructions for AI agents generating framework code. |
| [11-app-developer-guide.md](11-app-developer-guide.md) | App development guide covering `SOSSData`, datastore adapters, schemas, and service usage. |
| [12-reusable-app-patterns.md](12-reusable-app-patterns.md) | Reusable patterns for profiles, agent users, profile photos, chat identity, CMS app loading, caching, and validation. |
| [13-advanced-queries.md](13-advanced-queries.md) | Advanced `SOSSData::Query()` conditions, sorting, pagination, validation, and compatibility aliases. |
| [14-sossdata-query-firewall.md](14-sossdata-query-firewall.md) | SOSSData injection defenses, validation limits, prepared raw-query parameters, and blocked-response behavior. |

## Existing Reference Docs

The repository root also contains generated high-level references:

```text
README.md
DAVVAG_PROJECT_STRUCTURE.md
DAVVAG_TENATE_STRUCTURE.md
```

The tenant folder also contains a tenant-local guide:

```text
davvag-core/localhost/README.md
```

## Core Concepts

DAVVAG separates engine code from tenant/domain resources:

```text
Framework root:
C:\xampp\htdocs\git\davvag-core

Tenant root:
C:\xampp\htdocs\git\davvag-core\davvag-core\localhost
```

Root framework code handles routing, configuration, auth, data access, and plugin loading.

Tenant folders hold apps, workflows, schemas, local plugins, templates, and group-specific access files.

## Recommended Reading Order

1. Read [01-framework-overview.md](01-framework-overview.md).
2. Read [02-tenant-setup.md](02-tenant-setup.md).
3. Read [03-application-development.md](03-application-development.md).
4. For backend work, read [04-components-and-services.md](04-components-and-services.md) and [05-database-schemas.md](05-database-schemas.md).
5. For automation logic, read [06-workflows.md](06-workflows.md).
6. For datastore adapter details and app patterns, read [11-app-developer-guide.md](11-app-developer-guide.md).
7. For condition, sorting, and pagination payloads, read [13-advanced-queries.md](13-advanced-queries.md).
8. For query security and raw parameters, read [14-sossdata-query-firewall.md](14-sossdata-query-firewall.md).
9. For profile/user/chat/agent implementation patterns, read [12-reusable-app-patterns.md](12-reusable-app-patterns.md).
10. Before deployment, read [08-auth-sessions-permissions.md](08-auth-sessions-permissions.md) and [09-deployment.md](09-deployment.md).
