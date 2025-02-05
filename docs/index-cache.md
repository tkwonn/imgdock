# Indexing and Caching

This application primarily manages posts and tags.   
Specific indexes and Memcached-based caching have been implemented to improve query performance and reduce database load.

## No Index on the `tags` table

Reason: Data in tags is inserted and updated only by administrators; end users cannot create new tags.    
Therefore,  number of tags remains relatively small and changes infrequently. A full table scan on a small dataset does not pose a significant performance issue.

## Index on the `posts` table

- A composite index on (deleted_at, created_at) was added, named `idx_posts_deleted_created`.
- Many queries filter on `deleted_at IS NULL` and sort by `created_at DESC`.
- This index improves performance when listing non-deleted posts in descending order of creation, as well as when performing `COUNT` queries on non-deleted posts.

```sql
CREATE TABLE posts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    extension ENUM("jpg", "jpeg", "png", "gif") NOT NULL,
    size INT UNSIGNED NOT NULL,
    s3_key CHAR(21) NOT NULL UNIQUE,
    delete_key CHAR(16) NOT NULL UNIQUE,
    view_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP DEFAULT NULL,
    INDEX idx_posts_deleted_created (deleted_at, created_at)
);
```

## Memcached Caching

Query results that do not change frequently, are cached to reduce database load and improve response times.  

In this application, Memcached is specifically used for:
- Count Queries (e.g., `countAllPosts`, `countAllTags`): Often queried repeatedly but do not change unless new posts are added or existing ones are deleted.
- Tag Lookups (e.g., `findAllTags`, `findTagById`): When tags are modified (by an administrator), the cache can be invalidated or allowed to expire via a short TTL.

How it works:
- Check the Memcached cache for a key such as "all_tags".
- If a cached value is present and valid, return it immediately.
- If the key is missing or has expired, execute the database query, store the result in Memcached for a defined TTL (e.g., 60–300 seconds), then return the fresh data.

To confirm the cache is actually being used and see what keys exist in Memcached, phpMemcachedAdmin was installed.  
This tool displays slabs and items stored in Memcached, along with stats such as hits/misses.


