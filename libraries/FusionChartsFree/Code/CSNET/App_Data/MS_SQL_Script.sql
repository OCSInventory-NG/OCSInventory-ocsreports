SET NOCOUNT ON
-- Restricts volume of output to errors and
-- messages that use the PRINT function

USE [master]

IF EXISTS (SELECT name FROM master.dbo.sysdatabases WHERE name = N'FactoryDB')
	DROP DATABASE [FactoryDB]
GO

CREATE DATABASE [FactoryDB]
GO

USE [FactoryDB]

PRINT 'CREATING TABLE Factory_Master'
CREATE TABLE [Factory_Master] (
	[FactoryId] [int] NOT NULL ,
	[FactoryName] [nvarchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	CONSTRAINT [PK_Factory_Master] PRIMARY KEY  CLUSTERED 
	(
		[FactoryId]
	)  ON [PRIMARY] 
) ON [PRIMARY]
GO




PRINT 'CREATING TABLE Factory_Output'
CREATE TABLE [Factory_Output] (
	[FactoryID] [int] NULL ,
	[DatePro] [smalldatetime] NULL ,
	[Quantity] [float] NULL ,
	CONSTRAINT [FK_Factory_Output_Factory_Master] FOREIGN KEY 
	(
		[FactoryID]
	) REFERENCES [Factory_Master] (
		[FactoryId]
	)
) ON [PRIMARY]
GO


PRINT 'INSERTING DATA INTO TABLE Factory_Master'
ALTER TABLE [Factory_Master] NOCHECK CONSTRAINT ALL
INSERT INTO [Factory_Master] ([FactoryId],[FactoryName]) VALUES (1,'Factory 1')
INSERT INTO [Factory_Master] ([FactoryId],[FactoryName]) VALUES (2,'Factory 2')
INSERT INTO [Factory_Master] ([FactoryId],[FactoryName]) VALUES (3,'Factory 3')
ALTER TABLE [Factory_Master] CHECK CONSTRAINT ALL

PRINT 'INSERTING DATA INTO TABLE Factory_Output'
ALTER TABLE [Factory_Output] NOCHECK CONSTRAINT ALL
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/01 05:53:00 PM',21)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/02 05:54:00 PM',23)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/03 05:54:00 PM',22)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/04 05:54:00 PM',24)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/05 05:54:00 PM',32)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/06 05:54:00 PM',21)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/07 05:54:00 PM',34)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/08 05:55:00 PM',32)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/09 05:55:00 PM',32)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/10 05:55:00 PM',23)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/11 05:55:00 PM',23)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/12 05:55:00 PM',32)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/13 05:55:00 PM',53)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/14 05:55:00 PM',23)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/15 05:55:00 PM',26)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/16 05:55:00 PM',43)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/17 05:56:00 PM',16)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/18 05:56:00 PM',45)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/19 05:56:00 PM',65)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (1,'2003/01/20 05:56:00 PM',54)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/01 05:53:00 PM',121)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/02 05:54:00 PM',123)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/03 05:54:00 PM',122)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/04 05:54:00 PM',124)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/05 05:54:00 PM',132)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/06 05:54:00 PM',121)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/07 05:54:00 PM',134)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/08 05:55:00 PM',132)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/09 05:55:00 PM',132)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/10 05:55:00 PM',123)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/11 05:55:00 PM',123)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/12 05:55:00 PM',132)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/13 05:55:00 PM',153)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/14 05:55:00 PM',123)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/15 05:55:00 PM',126)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/16 05:55:00 PM',143)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/17 05:56:00 PM',116)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/18 05:56:00 PM',145)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/19 05:56:00 PM',165)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (2,'2003/01/20 05:56:00 PM',154)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/01 05:53:00 PM',54)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/02 05:54:00 PM',56)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/03 05:54:00 PM',89)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/04 05:54:00 PM',56)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/05 05:54:00 PM',98)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/06 05:54:00 PM',76)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/07 05:54:00 PM',65)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/08 05:55:00 PM',45)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/09 05:55:00 PM',75)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/10 05:55:00 PM',54)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/11 05:55:00 PM',75)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/12 05:55:00 PM',76)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/13 05:55:00 PM',34)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/14 05:55:00 PM',97)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/15 05:55:00 PM',55)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/16 05:55:00 PM',43)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/17 05:56:00 PM',16)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/18 05:56:00 PM',35)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/19 05:56:00 PM',78)
INSERT INTO [Factory_Output] ([FactoryID],[DatePro],[Quantity]) VALUES (3,'2003/01/20 05:56:00 PM',75)
ALTER TABLE [Factory_Output] CHECK CONSTRAINT ALL
