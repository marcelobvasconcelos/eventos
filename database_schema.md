# Database Schema for Event Management System

This document outlines the database schema for the Event Management System. The schema includes tables for users, events, event requests, assets, loans, and notifications. All tables use InnoDB engine for referential integrity support.

## Tables Overview

- **users**: Stores user information including roles (user/admin).
- **events**: Stores event details.
- **event_requests**: Handles user requests for events, with approval/rejection by admins.
- **assets**: Manages event assets (e.g., equipment, materials).
- **loans**: Tracks asset loans to users for specific events.
- **notifications**: Stores notifications sent to users.

## Detailed Table Definitions

### users
| Column       | Type          | Constraints                  | Description                          |
|--------------|---------------|------------------------------|--------------------------------------|
| id           | INT           | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for the user      |
| name         | VARCHAR(255)  | NOT NULL                     | Full name of the user                |
| email        | VARCHAR(255)  | NOT NULL, UNIQUE             | Email address (unique)               |
| password     | VARCHAR(255)  | NOT NULL                     | Hashed password                      |
| role         | ENUM('user', 'admin') | NOT NULL, DEFAULT 'user' | User role                            |
| created_at   | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Account creation timestamp          |

### events
| Column       | Type          | Constraints                  | Description                          |
|--------------|---------------|------------------------------|--------------------------------------|
| id           | INT           | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for the event     |
| name         | VARCHAR(255)  | NOT NULL                     | Event name                           |
| description  | TEXT          |                              | Event description                    |
| date         | DATETIME      | NOT NULL                     | Scheduled date and time              |
| location     | VARCHAR(255)  |                              | Event location                       |
| status       | ENUM('Pendente', 'Aprovado', 'Rejeitado', 'Concluido') | NOT NULL, DEFAULT 'Pendente' | Event status                         |
| created_by   | INT           | NOT NULL, FOREIGN KEY (users.id) | User who created the event          |
| created_at   | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Event creation timestamp            |

### event_requests
| Column       | Type          | Constraints                  | Description                          |
|--------------|---------------|------------------------------|--------------------------------------|
| id           | INT           | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for the request    |
| user_id      | INT           | NOT NULL, FOREIGN KEY (users.id) | Requesting user                      |
| event_id     | INT           | NOT NULL, FOREIGN KEY (events.id) | Requested event                      |
| status       | ENUM('Pendente', 'Aprovado', 'Rejeitado') | NOT NULL, DEFAULT 'Pendente' | Request status                       |
| request_date | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Request submission date              |
| approved_by  | INT           | FOREIGN KEY (users.id)       | Admin who approved/rejected (nullable) |
| approved_at  | TIMESTAMP     |                              | Approval/rejection timestamp (nullable) |

### assets
| Column             | Type          | Constraints                  | Description                          |
|--------------------|---------------|------------------------------|--------------------------------------|
| id                 | INT           | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for the asset     |
| name               | VARCHAR(255)  | NOT NULL                     | Asset name                           |
| description        | TEXT          |                              | Asset description                    |
| quantity           | INT           | NOT NULL, DEFAULT 1          | Total quantity available             |
| available_quantity | INT           | NOT NULL, DEFAULT 1          | Currently available quantity         |
| created_at         | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Asset creation timestamp            |

### loans
| Column      | Type          | Constraints                  | Description                          |
|-------------|---------------|------------------------------|--------------------------------------|
| id          | INT           | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for the loan      |
| asset_id    | INT           | NOT NULL, FOREIGN KEY (assets.id) | Loaned asset                         |
| user_id     | INT           | NOT NULL, FOREIGN KEY (users.id) | User borrowing the asset             |
| event_id    | INT           | NOT NULL, FOREIGN KEY (events.id) | Event for which asset is loaned     |
| loan_date   | DATETIME      | NOT NULL                     | Loan start date                      |
| return_date | DATETIME      |                              | Expected/actual return date (nullable) |
| status      | ENUM('Emprestado', 'Devolvido') | NOT NULL, DEFAULT 'Emprestado' | Loan status                          |
| created_at  | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Loan creation timestamp             |

### notifications
| Column     | Type          | Constraints                  | Description                          |
|------------|---------------|------------------------------|--------------------------------------|
| id         | INT           | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for the notification |
| user_id    | INT           | NOT NULL, FOREIGN KEY (users.id) | Recipient user                       |
| message    | TEXT          | NOT NULL                     | Notification message                 |
| sent_date  | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | When the notification was sent      |
| is_read    | BOOLEAN       | NOT NULL, DEFAULT FALSE      | Whether the notification has been read |

## Relationships and Referential Integrity

- **users.id** references:
  - event_requests.user_id (CASCADE on DELETE)
  - event_requests.approved_by (SET NULL on DELETE)
  - events.created_by (RESTRICT on DELETE)
  - loans.user_id (CASCADE on DELETE)
  - notifications.user_id (CASCADE on DELETE)

- **events.id** references:
  - event_requests.event_id (CASCADE on DELETE)
  - loans.event_id (CASCADE on DELETE)

- **assets.id** references:
  - loans.asset_id (CASCADE on DELETE)

All foreign keys enforce referential integrity with appropriate actions (CASCADE for dependent data, RESTRICT/SET NULL where logical).