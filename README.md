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
4. Login
- Use account credentials to log in (only given to select users at this time).

---

## Functionality  

### Current Features  

#### User Authentication:  
- Login system for Admins and Users.  
- Session-based access control to ensure secure interactions.  

#### Record Management:  
**Admins can:**  
- Create, edit, and delete records in collections like *Deeds, Wills, and Agreements*.  
- Manage user accounts (create accounts, with editing and deletion coming soon).  

**Users can:**  
- Create, search, and modify records within permitted collections.  

#### Collection Search:  
- Users can filter records by specific fields using text-based queries.  
- Supports partial matches via MongoDB's regex search.  

#### Navigation:  
- Users can navigate through large datasets using keyboard shortcuts (e.g., arrow keys).  

#### Error Handling:  
- Alerts users if required fields are missing during searches or data creation.  

---

### Upcoming Features  
- Role-based access expansion (e.g., public viewers).  
- Admin functionalities for advanced data analytics.  
- Enhanced UI for bulk record uploads and modifications.  

---

## Code Structure  

### Important Files  
- `index.php`: Login page.  
- `dashboard.php`: Main interface for record and user management.  
- `manage_records.php`: Record-specific CRUD operations.  
- `db_connect.php`: MongoDB connection setup.  
- `styles.css`: Styling for the front end.  

### Key Directories  
- `/assets/`: Contains stylesheets and JavaScript files.  
- `/vendor/`: Auto-generated directory for Composer dependencies.  
- `/views/`: Frontend templates for different pages.  

---

## Future Enhancements  

### Scalability:  
- Support for larger datasets by introducing indexing mechanisms.  

### Advanced Search:  
- Adding multi-field search and filter options.  

### Integration:  
- API support for external services.  

### Deployment:  
- Remote hosting capabilities for deployment.  
---

## Contact  

For any issues or contributions, please reach out to the project team at:  

- **Email**: [sydneyg2021@gmail.com](mailto:support@sydneyg2021@gmail.com)  
- **GitHub**: [Penfield Records Repository](#)  

---

**Disclaimer**: This is an alpha version of the application and is subject to updates and improvements.  

