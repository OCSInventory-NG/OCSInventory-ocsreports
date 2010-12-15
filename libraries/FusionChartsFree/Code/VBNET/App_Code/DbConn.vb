Imports Microsoft.VisualBasic
Imports System.Data.Odbc
Imports System.Data
Imports System.Web
Imports System.Configuration

Namespace DataConnection

    ''' <summary>
    ''' DataBase Connection Class.
    ''' </summary>
    Public Class DbConn
        Public connection As OdbcConnection
        Public ReadData As OdbcDataReader
        Public aCommand As OdbcCommand

        ''' <summary>
        ''' Data Connection and get Data Reader
        ''' </summary>
        ''' <param name="strQuery">SQL Query</param>
        Public Sub New(ByVal strQuery As String)
            Dim ConnectionString As String, connectionName As String

            ' MS Access DataBase Connection - Defined in Web.Config
            connectionName = "MSAccessConnection"

            ' SQL Server DataBase Connection - Defined in Web.Config
            ' connectionName = "SQLServerConnection";

            ' Creating Connection string using web.config connection string
            ConnectionString = ConfigurationManager.ConnectionStrings(connectionName).ConnectionString
            Try

                ' Creating OdbcConnection Oject
                connection = New OdbcConnection()

                ' Setting Conection String
                connection.ConnectionString = ConnectionString

                ' Open Connection
                connection.Open()

                ' get reader
                GetReader(strQuery)

            Catch ex As Exception
                HttpContext.Current.Response.Write(ex.Message)
            End Try

        End Sub

        ''' <summary>
        ''' Create an instance dataReader
        ''' </summary>
        ''' <param name="strQuery">SQL Query</param>
        ''' <remarks>Return type object of OdbcDataReader</remarks>
        Public Sub GetReader(ByVal strQuery As String)

            '  Create a Command object
            aCommand = New OdbcCommand(strQuery, connection)

            ' Create data reader object using strQuery string
            ReadData = aCommand.ExecuteReader(CommandBehavior.CloseConnection)

        End Sub
    End Class
End Namespace

