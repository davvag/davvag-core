# Task Manager Context

Use this context when changing the tenant app at `davvag-core/localhost/apps/task-tracker`. The app-local `AGENTS.md` remains the detailed implementation guide.

## Runtime Shape

The app is registered as `task-tracker` and displayed as Task Manager. The active screens are split into `projects`, `tasks`, `my-tasks`, `time-tracker`, `task-view`, `task-work-log-summery`, and `task-work-log-detailed`. The requested `summery` spelling is retained in the component and route identifier.

Frontend components call the `taskapi` service. The PHP implementation is `services/taskapi/service.php`, class `TaskManagerService`, and all persistence uses `SOSSData` with `task_manager_` namespaces.

## Task Type Contract

Each task stores one `taskType` string in `task_manager_tasks`. The schema allows up to 50 UTF-8 characters, while the service restricts values to the controlled taxonomy returned by `POST ListTaskTypes`:

```text
Support
Development
Quality Assurance
Bug Fix
Meeting
Research
Design
Documentation
Deployment
Maintenance
Training
Administration
Other
Uncategorized
```

New tasks require a type in both the browser and `SaveTask`. The service canonicalizes case-insensitive matches to the declared spelling and rejects unknown values. Read paths normalize missing or blank legacy values to `Uncategorized`, so tasks created before the schema change remain visible and reportable. New tasks imported by `TaskEmailClient` are categorized as `Support`.

The task list, My Tasks, Task View, and Time Tracker identify the linked task type. The taxonomy is owned by the backend; browser components must load it through `ListTaskTypes` rather than duplicating it.

## Work Log Reporting

`WorkLogSummary` and `WorkLogDetailed` share `prepareWorkLogReport()` and `schemas/task_manager_work_log_report.json`. Their request filters are:

```text
period: weekly | monthly | specific
startDate: YYYY-MM-DD
endDate: YYYY-MM-DD
projectId: optional positive integer
taskType: optional value returned by ListTaskTypes
```

The raw query joins work logs to tasks and projects. It applies the inclusive work-date range, optional project filter, optional task-type filter, and project-access `EXISTS` condition before PHP aggregation. Missing task types are mapped to `Uncategorized` in SQL so that category can also be filtered.

Both report routes preserve `period`, `startDate`, `endDate`, `projectId`, and `taskType` when navigating between Summary and Detailed. The summary task nodes and detailed rows expose `taskType`; exact minute totals must remain identical between both endpoints for the same effective filters and profile permissions.

## Change Checklist

When changing task types, keep these locations aligned:

1. `TaskManagerService::taskTypes()` and `canonicalTaskType()`.
2. `schemas/task_manager_tasks.json` for persisted task fields.
3. `schemas/task_manager_work_log_report.json` for joined report fields and filters.
4. Task creation/edit UI and both report components.
5. `services/taskapi/component.json` when adding or renaming endpoints.

Validate PHP syntax, JavaScript syntax, every affected JSON schema/descriptor, and the raw-query placeholder contract before browser testing.
