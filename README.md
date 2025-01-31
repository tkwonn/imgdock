# imgdock

[![GitHub last commit](https://img.shields.io/github/last-commit/tkwonn/imgdock?color=chocolate)](https://github.com/tkwonn/imgdock/commits/)

## What is this

Similar to [imgur](https://imgur.com/), this web application allows users to upload, share, and view images without requiring a user account. It's useful in the following situations:

- Upload images: Users can easily upload images through a simple web interface. 
- Share images with others: After uploading, the service generates a unique URL for each image, which can be shared with others.
- View images: Anyone with the link can view the image directly without needing to log in or create an account.

Supported file types are `jpg`, `jpeg`, `png`, and `gif`.

URL: [imgdock.com](https://imgdock.com)

<br>

## Table of Contents

1. [Demo](#demo)
2. [Built with](#built-with)
3. [ER Diagram](#er-diagram)
4. [Cloud Architecture Diagram](#cloud-architecture-diagram)
5. [Security Measures](#security-measures)
6. [CI/CD](#cicd)
    1. [Continuous Integration](#continuous-integration)
    2. [Continuous Deployment](#continuous-deployment)
7. [How to use](#how-to-use)

<br>

## Demo

- Upload (tag)
- View (with link, sort selection, waterfall view)
- Delete


<br>

## Built with

| **Category** | **Technology**                                                                                            |
|--------------|-----------------------------------------------------------------------------------------------------------|
| VM           | Amazon EC2                                                                                                |
| Web server   | Nginx                                                                                                     |
| Frontend     | HTML/CSS, TypeScript, Bootstrap CSS, Vite (Build tool)                                                    |
| Backend      | PHP 8.2                                                                                                   |
| Database     | Amazon RDS (MySQL 8.0)                                                                                    |
| In-memory DB | memcached [(Learn more about its usage)](https://github.com/tkwonn/imgdock/blob/main/docs/index-cache.md) |
| Storage      | Amazon S3, MinIO (for local development)                                                                  |
| Middleware   | [Custom-built migration tool](https://github.com/tkwonn/imgdock/blob/main/docs/migration-tool.md)         |
| CI/CD        | GitHub Actions                                                                                            |
| Container    | Docker                                                                                                    |

<br>

## ER Diagram

[screenshot]

- `posts`: Stores metadata for uploaded files (actual files are stored in S3)
- `tags`: Table for managing tags and their descriptions
- `post_tags`: Junction table implementing many-to-many relationship between posts and tags

## Storage Structure

To organize uploaded images efficiently and ensure scalability, the application stores images in an Amazon S3 bucket using a structured folder and naming convention. The object key follows the format:

`<year>/<month>/<unique-string>.<extension>`

For example:   
`2025/01/SharyagI.png`

Time-Based Organization:
- The year/month prefix groups images by their upload date, simplifying management and enabling lifecycle policies (e.g., archiving or deleting old files).

Unique String Generation:
- The `<unique-string>` is generated using a cryptographically secure random ID via PHP's `random_bytes()` function, which sources entropy from `/dev/urandom`.
- It uses a 64-character URL-safe alphabet (0-9, A-Z, a-z, -, _) and is 8 characters long, providing 64⁸ = 281,474,976,710,656 possible combinations.
- This makes collisions extremely unlikely, so the implementation relies solely on a database UNIQUE constraint without additional collision handling.

<br>

## Cloud Architecture Diagram


<br>

## Security Measures

### File Size and Upload Limits

To ensure efficient resource usage and prevent abuse, the application enforces specific file size and upload limits. These limits are configured at multiple levels:

1. Frontend Restrictions (Uppy Library)
- The maximum file size for non-animated images (e.g., `jpg`, `jpeg`, `png`) is 10MB. 
- For animated images (e.g., `gif`), the limit is 20MB.

2. Backend Restrictions (PHP)
- `upload_max_filesize`: 20 MB (maximum size of a single uploaded file).
- `post_max_size`: 20 MB (maximum size of the entire POST request).
- `memory_limit`: 256 MB (maximum memory allocated to PHP scripts).
- `max_execution_time`: 300 seconds (maximum time a script can run).

3. Web Server Restrictions (Nginx)
- `client_max_body_size`: 20 MB (maximum size of the client request body).
- Rate Limiting:
  - File upload requests are rate-limited to 1 request per second with a burst of 5 requests. 
  - This prevents abuse and ensures fair usage of the service.

### Secure S3 Bucket Policies

<br>

## CI/CD

The project uses GitHub Actions to automate testing and deployment workflows with the following configurations:

### Continuous Integration

- Dependency caching using Composer to speed up builds
- Code quality checks using PHP CS Fixer

### Continuous Deployment

- Secure AWS Authentication using OpenID Connect (short-lived tokens)
- Minimal IAM permissions to ensure secure cloud role operations
- AWS Systems Manager (SSM) for secure remote command execution (no direct SSH access or security group changes)

<br>

## How to use

This project uses Docker for local development, making it easy for anyone to run and test the application on their local machine.

1. Clone this repository
```bash
git clone https://github.com/tkwonn/imgdock.git
cd imgdock
```

2. Setup the environment variables
```bash
# Copy the example environment file
cp .env.example .env
```

3. Build and run the containers
```bash
make build
make up

# Initialize the database
make db/migrate
make db/seed
```

The application will be available at `http://localhost:8080`



