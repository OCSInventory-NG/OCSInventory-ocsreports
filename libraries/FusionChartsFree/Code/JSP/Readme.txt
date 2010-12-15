This version works with MySQL database whose database-creation scripts are provided along with the web application in the DB folder.
We have used JSP 2.0 and jdk1.6.0_10 for the development of this demo.

Prerequisites:
1. Java version - jdk6

2. Apache Tomcat version - jakarta-tomcat-6 

3. MySQL 5.0

Installation Instructions:
1. Java version - jdk6 which can be downloaded from the following link:
http://java.sun.com/javase/downloads/index.jsp

2. After downloading and installing java,JAVA_HOME environment variable has to be set to the base path of the JDK.
To set this variable on Windows do the following:
Go to MyComputer, right click to view properties -> advanced tab ->environment variables 
Set a new Variable name: JAVA_HOME with Value as the installation directory. For eg: 
C:\jdk1.6.0_10

3. Apache Tomcat version - tomcat6.0 which can be downloaded from the following link:
http://tomcat.apache.org/download-60.cgi

Click on the core zip file. Download it.Extract it to some folder. Configure tomcat as per the tomcat docs.

4. Copy the FusionChartsFree_JSP.war present in Deployable folder to "CATALINA_HOME"/webapps folder when the tomcat server is running.

5. In order to configure the MySQL database:
   Open the file "CATALINA_HOME"/webapps/FusionChartsFree_JSP/META-INF/context.xml.	
   In this xml,please change the username,password,url according to your database.

6.If you are using MySQL as the database, please start your MySQL instance.

7.Start the tomcat server.

8.Access the application by opening the browser window with the following address:
http://localhost:8080/FusionChartsFree_JSP/JSP/default.htm


Note: "CATALINA_HOME" refers to the installation directory of Tomcat

---

For source code of the java files please see the folder SourceCode.

For javadoc of the source files please open index.html in the folder SourceCode/doc.