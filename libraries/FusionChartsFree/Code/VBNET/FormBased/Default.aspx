<%@ Page Language="VB" %>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<script runat="server">

    ' This Form Based Example Program...
    
    ' Event to transfer page control to FormSubmit.aspx
    Public Sub dosubmit(ByVal sender As Object, ByVal e As EventArgs)
        ' Storing Eatch ASP:TextBox Text into Context items
        Context.Items("Soups") = Soups.Text
        Context.Items("Salads") = Salads.Text
        Context.Items("Sandwiches") = Sandwiches.Text
        Context.Items("Beverages") = Beverages.Text
        Context.Items("Desserts") = Desserts.Text
        
        ' Submit form 
        Server.Transfer("Chart.aspx")
    End Sub
</script>

<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Form Based Data Charting Example
	</TITLE>
	<style type="text/css">
	<!--
	body {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	.text{
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	-->
	</style>
</HEAD>
<BODY>

<%
'In this example, we first present a form to the user, to input data.
'For a demo, we present a very simple form intended for a Restaurant to indicate
'sales of its various product categories at lunch time (for a week). 
'The form is rendered in this page (Default.asp). It submits its data to
'Chart.asp. In Chart.asp, we retrieve this data, convert into XML and then
'render the chart.

'So, basically this page is just a form. 
%>
<CENTER>
	<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Form-Based Data Example</h2>
	<p class='text'>Please enter how many items of each category you sold within this week. We'll plot this data on a Pie chart. </p>
	<p class='text'>To keep things simple, we're not validating for non-numeric data here. So, please enter valid numeric values only. In your real-world applications, you can put your own validators.</p>
	<form id="form1" runat="server">
	<table width='50%' align='center' cellpadding='2' cellspacing='1' border='0' class='text'>
		<tr>
			<td width='50%' align='right'>
				<B>Soups:</B> &nbsp;
			</td>
			<td width='50%'>
                <asp:TextBox ID="Soups" runat="server" 
                     Width="47px" CssClass="text">108</asp:TextBox>
				&nbsp;bowls
			</td>
		</tr>
		<tr>
			<td width='50%' align='right'>
				<B>Salads:</B> &nbsp;
			</td>
			<td width='50%'>
                <asp:TextBox ID="Salads" runat="server" 
                     Width="47px" CssClass="text">162</asp:TextBox>&nbsp;plates
			</td>
		</tr>
		<tr>
			<td width='50%' align='right'>
				<B>Sandwiches:</B> &nbsp;
			</td>
			<td width='50%'>
                <asp:TextBox ID="Sandwiches" runat="server" 
                     Width="47px" CssClass="text">360</asp:TextBox>
                pieces
			</td>
		</tr>
		<tr>
			<td width='50%' align='right'>
				<B>Beverages:</B> &nbsp;
			</td>
			<td width='50%'>
                <asp:TextBox ID="Beverages" runat="server" 
                     Width="47px" CssClass="text">171</asp:TextBox>&nbsp;cans &nbsp;</td>
		</tr>
		<tr>
			<td width='50%' align='right'>
				<B>Desserts:</B> &nbsp;
			</td>
			<td width='50%'>
                <asp:TextBox ID="Desserts" runat="server" 
                     Width="47px" CssClass="text">99</asp:TextBox>&nbsp;plates
			</td>
		</tr>
		<tr>
			<td width='50%' align='right'>&nbsp;
				
			</td>
			<td width='50%'>
				<asp:Button ID="Button1" runat="server" 
                    Text="Chart it!"  OnClick="dosubmit" />
			</td>
		</tr>
	</table>
	</form>
	<BR><H5 ><a href='../default.aspx'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>
