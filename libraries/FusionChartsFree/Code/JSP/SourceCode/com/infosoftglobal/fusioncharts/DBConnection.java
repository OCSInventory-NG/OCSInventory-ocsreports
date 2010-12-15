/*
 * Created on Oct 25, 2006
 *
 */
package com.infosoftglobal.fusioncharts;

import java.sql.Connection;
import java.sql.SQLException; 

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

/**
 * 
 * Contains methods to get a connection to the database.<br>
 * The database used here is MySQL<br>
 * This class contains code to connect to the database<br>
 * The DSN looked up for connection is "jdbc/FactoryDB". Please configure the same DSN in your server.<br>
 * Only one instance of this class should ideally be used throughout your application.<br>
 * For demo purpose, we have kept the default constructor available to all classes.<br>
 * Ideally you would override the default constructor and write a getInstance method which will<br>
 * return a single DBConnection instance always,thus making it a singleton class.<br>
 * 
 * @author InfoSoft Global (P) Ltd.
 */
public class DBConnection {
    
    
    /**
     * Returns a connection to a database as configured earlier.<br>
     * This method can be used by all the jsps to get a connection.<br>
     * The jsps do not have to worry about which db to connect to etc.<br>
     * This has been configured in this class by the InitServlet.<br>
     * @return Connection - a connection to the specific database based on the configuration
     */
    public Connection getConnection() {
	Connection oConn=null;

	    // Connection to the mySQL DB
	    oConn = getConnectionByDSN("jdbc/FactoryDB");

	return oConn;
    }
   
    
    /**
     * Opens the connection to the MySQL Database<br>
     * Using the DataSource Name specified in the config file of the server in which it is deployed.<br>
     * In real world applications,some sort of connection pooling mechanism <br>
     * would be used for getting the connection and proper care would be taken to<br>
     * close it when the work is done.<br>
     *  
     * @return Connection a connection to the MySQL database
     */
    private Connection getConnectionByDSN(String dataSourceName) {
	Connection oConn = null;

	try {
    	  	Context initContext = new InitialContext();
    	  	Context envContext  = (Context)initContext.lookup("java:/comp/env");
    	  	DataSource ds = (DataSource)envContext.lookup(dataSourceName);//"jdbc/FactoryDB"
    	  	oConn = ds.getConnection();
    
	} catch (SQLException e) {
	    // TODO Auto-generated catch block
	    e.printStackTrace();
	} catch (NamingException e) {
	    // TODO Auto-generated catch block
	    e.printStackTrace();
	}
	return oConn;
    }


}
