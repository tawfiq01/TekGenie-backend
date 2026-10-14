# 00 — Document Index

| Field | Value |
|---|---|
| Project | Modern E-Commerce Website (Bangladesh) — working name **[Store Name]** |
| Document | Documentation Index & Control Register |
| Version | 0.3 (Draft) |
| Date | 2026-10-13 |
| Prepared by | Claude (acting Senior BA / Solution Architect) |
| Approver | [Product Owner — to be named] |

## 1. Purpose

This index is the single register of all project documents. It records each document's authoritative file, version, status, owner and dependencies. The **authoritative version** of every document is the file listed here, in the `docs/` directory of the project repository, at the version shown. Copies exported to Word/PDF for review are non-authoritative.

## 2. Status definitions

| Status | Meaning |
|---|---|
| Not started | No draft exists |
| Draft | Prepared, not yet reviewed |
| In review | Shared with stakeholders for comment |
| Approved | Signed off by the approver; changes require a version bump and change note |
| Superseded | Replaced by a newer document |

Versioning: `0.x` = drafts before approval, `1.0` = first approved baseline, `1.x` = approved minor revisions, `2.0` = major re-baseline.

## 3. Document register

| ID | Document | File | Version | Status | Owner | Depends on |
|---|---|---|---|---|---|---|
| DOC-00 | Document Index | `00-document-index.md` | 0.3 | Draft | BA | — |
| DOC-01 | Business Requirements Document (BRD) | `01-business-requirements.md` | 0.2 | In review | BA / Product Owner | — |
| DOC-02 | Software Requirements Specification (SRS) | `02-software-requirements-specification.md` | 0.2 | In review | BA / Solution Architect | DOC-01 |
| DOC-03 | Use Cases & Workflows | `03-use-cases-and-workflows.md` | 0.1 | Draft | BA | DOC-02 |
| DOC-04 | UI/UX Design Specification (+ Design canvas prototype) | `04-ui-ux-specification.md` | 0.1 | Draft | UI/UX Designer | DOC-01, DOC-02 |
| DOC-05 | Software Architecture Document (SAD) | `05-software-architecture.md` | 0.1 | Draft | Solution Architect | DOC-02 |
| DOC-06 | Database Design Document | `06-database-design.md` | 0.1 | Draft | Database Architect | DOC-02, DOC-05 |
| DOC-07 | Project Plan & WBS | `07-project-plan-and-wbs.md` | 0.1 | Draft | Project Manager | DOC-02, DOC-05 |
| DOC-08 | Test Strategy & Test Cases | `08-test-strategy-and-test-cases.md` + `08-test-cases.csv` (173 cases; CSV is authoritative for cases) | 0.1 | Draft | QA Engineer | DOC-02, DOC-03 |
| DOC-09 | API & Integration Specification | `09-api-and-integration-specification.md` | 0.1 | Draft | Solution Architect | DOC-05 |
| DOC-10 | Deployment, Security & Operations Guide | `10-deployment-security-operations.md` | — | Not started | DevOps | DOC-05 |
| DOC-11 | Requirement Traceability Matrix (RTM) | `11-requirement-traceability-matrix.csv` (168 rows: 125 FR + 43 NFR) | 0.1 | Draft | BA / QA | DOC-01, DOC-02, DOC-08 |
| DOC-12 | User & Administrator Manual | `12-user-and-admin-manual.md` | — | Not started | BA | Built system |

Diagrams (Mermaid sources and exported images) live in `docs/diagrams/`.

UI prototype (companion to DOC-04): Design canvas at https://claude.ai/artifact/KbBTDvgrnq877DZE8nCAbn (v0.1).

## 4. Planned sequence

1. DOC-01 BRD and DOC-02 SRS → review and approval (approved to proceed; formal sign-off pending)
2. DOC-03 Use Cases, DOC-04 UI/UX, DOC-05 SAD, DOC-06 Database → architecture and UI prototype approval (approved to proceed)
3. DOC-07 Project Plan, DOC-08 Test Strategy, DOC-09 API spec, DOC-11 RTM (first pass) (**current step** — then baseline approval of DOC-01…DOC-11 before implementation)
4. Implementation (phases in DOC-07)
5. DOC-10 Deployment Guide, DOC-12 Manuals, final RTM update at UAT

## 5. Change log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1 | 2026-10-13 | Claude | Initial register created |
| 0.2 | 2026-10-13 | Claude | Added DOC-03 to DOC-06 drafts; BRD/SRS to v0.2 |
| 0.3 | 2026-10-13 | Claude | Added DOC-07, DOC-08 (+CSV), DOC-09, DOC-11 drafts |
