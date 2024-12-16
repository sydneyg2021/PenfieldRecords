# **Penfield Records Management System**

## **Project Overview**

The Penfield Records Management System is a web-based application designed to enable users to manage and maintain legal documents, such as deeds, wills, and agreements, in a secure and scalable MongoDB database. The system is built to cater to both technical and non-technical users, providing a user-friendly interface for creating, editing, and managing records. Key features include user authentication, record management, and a robust search mechanism to ensure quick access to relevant documents.

---

## **Scope**

The project aims to:

1. Provide a centralized platform for managing legal documents with CRUD (Create, Read, Update, Delete) capabilities.
2. Offer an intuitive interface that accommodates both technical administrators and non-technical users.
3. Ensure data reliability, fault tolerance, and high availability through MongoDB sharding and replication.
4. Support non-repudiation and error handling to maintain system integrity.
5. Facilitate role-based access, with distinct privileges for admins and regular users.

Future enhancements include:
- The ability to assign permissions to new roles.
- Advanced analytics on record usage.
- Integration with external services for document validation.

---

## **Technology Stack**

1. **Frontend:** HTML, CSS, JavaScript.
2. **Backend:** PHP.
3. **Database:** MongoDB (cloud-hosted via MongoDB Atlas).
4. **Other Dependencies:** Composer for managing PHP packages.
5. **Deployment:** Local server (via XAMPP/WAMP) or cloud-hosted.

---

## **Dependencies**

### Backend
- **PHP** (version 7.4 or later)
- **MongoDB PHP Library:** For interacting with the MongoDB database.
  - Installed via Composer:
    ```bash
    composer require mongodb/mongodb
    ```

### Database
- **MongoDB Atlas Cluster:** Cloud-based database hosting.

### Frontend
- **CSS Framework:** Custom styling.

### Development Tools
- **Web Server:** Apache (part of XAMPP or WAMP stack).
- **Composer:** For managing PHP dependencies.

---

## **How to Run Locally**

### Prerequisites
1. Ensure PHP and Composer are installed on your system.
2. Install XAMPP or WAMP to provide a local web server and PHP environment.
3. Clone this repository to your local machine:
   ```bash
   git clone https://github.com/your-repo/PenfieldRecords.git
