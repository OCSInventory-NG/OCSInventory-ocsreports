-- Add CAS variables to config table

INSERT IGNORE INTO `config` (NAME, IVALUE, TVALUE, COMMENTS)
VALUES  ('CAS_PORT',NULL, '443','CAS server port, e.g. : 443'),
        ('CAS_URI',NULL,'/cas','CAS server uri, e.g : /cas'),
        ('CAS_HOST',NULL,'','CAS server host, e.g. : authentication.org'),
        ('CAS_DEFAULT_ROLE',NULL,'read-only','CAS default role, applied on first connection initiated using CAS authentication'),
        ('CAS_BASEURL',NULL,'','Server URL as seen by CAS IdP, e.g. : https://ocs.example.com/ocsreports'),
        ('CAS_SERVER_CA_CERT_PATH',NULL,'','Path to IdP Server CA Certificate for server validation function, e.g. : files/certificates/CA.pem');