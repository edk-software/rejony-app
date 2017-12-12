UPDATE
  cantiga_edk_participants t,
  (
    SELECT
      id,
      LEFT(lastName, 1) as lastN,
      LEFT(firstName, 10) as firstN
    from
      cantiga_edk_participants
  ) t1
SET
  t.email = concat('user', t1.id, '@edk.org.pl'),
  t.lastName = t1.lastN,
  t.firstName = t1.firstN
WHERE
  t.id = t1.id;

UPDATE
  cantiga_users t,
  (
    SELECT
      id,
      CONCAT('login', id) as login
    from
      cantiga_users
  ) t1
SET
  t.email = concat(t1.login, '@edk.org.pl'),
  t.login = t1.login,
  t.name = t1.login,
  t.avatar = NULL
WHERE
  t.id = t1.id;

UPDATE
  cantiga_user_registrations t,
  (
    SELECT
      id,
      CONCAT('login', id) as login
    from
      cantiga_user_registrations
  ) t1
SET
  t.email = concat(t1.login, '@edk.org.pl'),
  t.login = t1.login,
  t.name = t1.login
WHERE
  t.id = t1.id;

UPDATE
  cantiga_invitations t,
  (
    SELECT
      id,
      CONCAT('login', id) as login
    from
      cantiga_invitations
  ) t1
SET
  t.email = concat(t1.login, '@edk.org.pl')
WHERE
  t.id = t1.id;

UPDATE
  cantiga_contacts t,
  (
    SELECT
      userid,
      CONCAT('login', userid) as login
    from
      cantiga_contacts
  ) t1
SET
  t.email = concat(t1.login, '@edk.org.pl'),
  t.telephone = 999999999
WHERE
  t.userid = t1.userid;

UPDATE
  cantiga_edk_messages t,
  (
    SELECT
      id,
      CONCAT('login', id) as login
    from
      cantiga_edk_messages
  ) t1
SET
  t.authorName = t1.login,
  t.authorEmail = concat(t1.login, '@edk.org.pl')
WHERE
  t.id = t1.id;

UPDATE
  cantiga_credential_changes t,
  (
    SELECT
      id,
      CONCAT('login', id) as login
    from
      cantiga_credential_changes
  ) t1
SET
  t.email = concat(t1.login, '@edk.org.pl')
WHERE
  t.id = t1.id;

UPDATE
  cantiga_courses
SET
  authorName = 'EDK',
  authorEmail = 'edk@edk.org.pl';
