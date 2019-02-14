-- Create full text indexes for softwares name and publisher
CREATE FULLTEXT INDEX publisher_ft_idx ON softwares (PUBLISHER);
CREATE FULLTEXT INDEX name_ft_idx ON softwares (NAME);
