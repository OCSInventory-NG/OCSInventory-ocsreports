-- Add CAS variables to config table

INSERT IGNORE INTO `config` (NAME, IVALUE, TVALUE, COMMENTS)
VALUES  ('CAS_BASEURL',0,'','Server URL as seen by CAS IdP, e.g. : https://ocs.example.com/ocsreports'),
        ('CAS_SERVER_CA_CERT_PATH',0,'','Path to IdP Server CA Certificate for server validation function, e.g. : files/certificates/CA.pem');