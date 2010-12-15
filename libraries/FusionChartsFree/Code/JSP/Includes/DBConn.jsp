<%@page import="java.sql.Connection,java.sql.DriverManager,javax.naming.Context,javax.naming.InitialContext,javax.naming.NamingException,javax.sql.DataSource"%><% 
	     /*In this page, we open the connection to the Database
	     */
	    Connection oConn = null;
		    /*In this page, we open the connection to the Database
		     Our MySQL database.
		     It's a very simple database with just 2 tables (for the sake of demo).
		     Ideally the Connection to the database should be made in a Java class or using Connection Pooling component
		     Here the Data Source name comes from the web.xml.
	        */
	        oConn = null;

			try {
				   Context initContext = new InitialContext();
				   Context envContext  = (Context)initContext.lookup("java:/comp/env");
			       DataSource ds = (DataSource)envContext.lookup("jdbc/FactoryDB");
			       oConn = ds.getConnection();
			} catch (java.sql.SQLException e) {
			    // TODO Auto-generated catch block
			    e.printStackTrace();
			} catch (NamingException e) {
			    // TODO Auto-generated catch block
			    e.printStackTrace();
			}%>