using System;
using System.Data;
using System.Data.Odbc;
using System.Web;
using System.Configuration;

namespace DataConnection
{
    /// <summary>
    /// DataBase Connection Class.
    /// </summary>
    public class DbConn
    {

        //  Create a database Connection. using here Access Database
        //  Return type object of OdbcConnection

        public OdbcConnection connection;
        public OdbcDataReader ReadData;
        public OdbcCommand aCommand;
        /// <summary>
        /// Data Connection and get Data Reader
        /// </summary>
        /// <param name="strQuery">SQL Query</param>
        public DbConn(string strQuery)
        {
            // MS Access DataBase Connection - Defined in Web.Config
            string connectionName = "MSAccessConnection";

            // SQL Server DataBase Connection - Defined in Web.Config
            //string connectionName = "SQLServerConnection";

            // Creating Connection string using web.config connection string
            string ConnectionString = ConfigurationManager.ConnectionStrings[connectionName].ConnectionString;
            try
            {
                // create connection object
                connection = new OdbcConnection();
                // set connection string
                connection.ConnectionString = ConnectionString;
                // open connection
                connection.Open();
                // get reader
                GetReader(strQuery);
            }
            catch (Exception e)
            {
                HttpContext.Current.Response.Write(e.Message.ToString());
            }

        }

        // Create an instance dataReader
        // Return type object of OdbcDataReader
        /// <summary>
        /// Get Data Reader
        /// </summary>
        /// <param name="strQuery">SQL Query</param>
        public void GetReader(string strQuery)
        {
            //  Create a Command object
            aCommand = new OdbcCommand(strQuery, connection);

            // Create data reader object using strQuery string
            // Auto close connection
            ReadData = aCommand.ExecuteReader(CommandBehavior.CloseConnection);

        }

    }
}