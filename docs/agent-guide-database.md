# Agent Guide: Database Interaction

This guide establishes the standard patterns for interacting with the database in CerneCMS.

**CRITICAL RULE**: The application uses **PDO (PHP Data Objects)** for its database connection, *not* the native `SQLite3` class. Mixing these two API styles causes fatal errors (e.g., `Call to member function fetchArray() on true`).

## 1. Accessing the Database

Always access the database instance via Flight:

```php
$db = Flight::db(); // Returns a \PDO instance
```

## 2. Standard Patterns

### A. Fetching Data (SELECT)

**❌ INCORRECT (SQLite3 Style - WILL DATA):**
```php
// DO NOT DO THIS
$res = $stmt->execute();
// Fails because execute() returns bool(true) in PDO, clearly not an object with fetchArray()
while ($row = $res->fetchArray(SQLITE3_ASSOC)) { ... }
```

**✅ CORRECT (PDO Style):**
```php
$stmt = $db->prepare("SELECT * FROM users WHERE status = :status");
$stmt->bindValue(':status', 'active', \PDO::PARAM_STR);
$stmt->execute(); // Returns true/false

// Fetch directly from the statement
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    // Process $row
}

// Or fetch a single row
$user = $stmt->fetch(\PDO::FETCH_ASSOC);
```

### B. Inserting/Updating Data

**❌ INCORRECT (SQLite3 Constants):**
```php
$stmt->bindValue(':id', $id, SQLITE3_INTEGER); // Undefined constant or wrong type
```

**✅ CORRECT (PDO Constants):**
```php
$stmt = $db->prepare("INSERT INTO items (name, count) VALUES (:name, :count)");
$stmt->bindValue(':name', $name, \PDO::PARAM_STR);
$stmt->bindValue(':count', $count, \PDO::PARAM_INT);

if ($stmt->execute()) {
    $newId = $db->lastInsertId();
}
```

## 3. Cheatsheet

| Concept | SQLite3 (Generic/Wrong) | PDO (Correct for CerneCMS) |
| :--- | :--- | :--- |
| **Connection** | `new SQLite3(...)` | `Flight::db()` (PDO) |
| **Constants** | `SQLITE3_TEXT`, `SQLITE3_INTEGER` | `\PDO::PARAM_STR`, `\PDO::PARAM_INT` |
| **Execution** | `$res = $stmt->execute(); $res->fetch...` | `$stmt->execute(); $stmt->fetch...` |
| **Fetch Row** | `$res->fetchArray(SQLITE3_ASSOC)` | `$stmt->fetch(\PDO::FETCH_ASSOC)` |
| **Fetch One** | `$db->querySingle(...)` | `$stmt->fetchColumn()` |

## 4. Troubleshooting

**Error: `Call to a member function fetchArray() on true`**
*   **Cause**: You tried to call a method on the result of `$stmt->execute()`. In PDO, `execute()` returns a boolean.
*   **Fix**: Call `fetch()` on the `$stmt` object itself, not the result of execute.

**Error: `Undefined constant "SQLITE3_INTEGER"`**
*   **Cause**: You are using native SQLite3 constants.
*   **Fix**: Use `\PDO::PARAM_INT` instead.
