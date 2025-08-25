ClipWave

A tiny, deployment-focused video sharing app. Creators can upload clips; viewers can watch, like, and comment. Built to demonstrate CI/CD to Azure with a simple PHP stack.

Features

Auth: Register / Login / Logout (sessions)

Roles: Creator (upload) vs Viewer (like/comment)

Creator Dashboard: grid of your videos + “Add video” panel

Home feed: video cards with inline likes & comments (AJAX)

SweetAlert UX for success/errors and login prompts

Contact page (stores messages in DB)

Tech Stack

PHP 8 + Apache (no framework)

MySQL/MariaDB

DDEV for local dev (Docker)

Dockerfile for production container

GitHub Actions → Azure Web App (Linux, custom container)

(Optional) Azure Blob Storage for media
