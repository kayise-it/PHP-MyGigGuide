-- Populate venue_owners from existing venues
-- This script adds primary owners based on venue.user_id or venue.owner_id

-- First, populate from user_id
INSERT IGNORE INTO venue_owners (venue_id, user_id, role, added_by_user_id, added_at, created_at, updated_at)
SELECT 
    v.id as venue_id,
    v.user_id as user_id,
    'primary' as role,
    v.user_id as added_by_user_id,
    COALESCE(v.created_at, NOW()) as added_at,
    NOW() as created_at,
    NOW() as updated_at
FROM venues v
WHERE v.user_id IS NOT NULL
  AND EXISTS (SELECT 1 FROM users u WHERE u.id = v.user_id);

-- Then, populate from owner_id where owner_type is User
INSERT IGNORE INTO venue_owners (venue_id, user_id, role, added_by_user_id, added_at, created_at, updated_at)
SELECT 
    v.id as venue_id,
    v.owner_id as user_id,
    'primary' as role,
    v.owner_id as added_by_user_id,
    COALESCE(v.created_at, NOW()) as added_at,
    NOW() as created_at,
    NOW() as updated_at
FROM venues v
WHERE v.owner_id IS NOT NULL
  AND v.owner_type = 'App\\Models\\User'
  AND NOT EXISTS (SELECT 1 FROM venue_owners vo WHERE vo.venue_id = v.id AND vo.user_id = v.owner_id)
  AND EXISTS (SELECT 1 FROM users u WHERE u.id = v.owner_id);

-- Populate from Artist owners
INSERT IGNORE INTO venue_owners (venue_id, user_id, role, added_by_user_id, added_at, created_at, updated_at)
SELECT 
    v.id as venue_id,
    a.user_id as user_id,
    'primary' as role,
    a.user_id as added_by_user_id,
    COALESCE(v.created_at, NOW()) as added_at,
    NOW() as created_at,
    NOW() as updated_at
FROM venues v
INNER JOIN artists a ON v.owner_id = a.id
WHERE v.owner_id IS NOT NULL
  AND (v.owner_type = 'App\\Models\\Artist' OR v.owner_type LIKE '%Artist%')
  AND a.user_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM venue_owners vo WHERE vo.venue_id = v.id AND vo.user_id = a.user_id)
  AND EXISTS (SELECT 1 FROM users u WHERE u.id = a.user_id);

-- Populate from Organiser owners
INSERT IGNORE INTO venue_owners (venue_id, user_id, role, added_by_user_id, added_at, created_at, updated_at)
SELECT 
    v.id as venue_id,
    o.user_id as user_id,
    'primary' as role,
    o.user_id as added_by_user_id,
    COALESCE(v.created_at, NOW()) as added_at,
    NOW() as created_at,
    NOW() as updated_at
FROM venues v
INNER JOIN organisers o ON v.owner_id = o.id
WHERE v.owner_id IS NOT NULL
  AND (v.owner_type = 'App\\Models\\Organiser' OR v.owner_type LIKE '%Organiser%')
  AND o.user_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM venue_owners vo WHERE vo.venue_id = v.id AND vo.user_id = o.user_id)
  AND EXISTS (SELECT 1 FROM users u WHERE u.id = o.user_id);

