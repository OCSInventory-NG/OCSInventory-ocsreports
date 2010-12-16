# Contains functions to generate chart by
# * adopting the dataURL method of FusionCharts and using render_chart function from the helper class.
# * adopting  the dataXML method of FusionCharts and using render_chart function from the helper class.
# * adopting the dataURL method of FusionCharts and using render_chart_html function from the helper class.
# * adopting the dataXML method of FusionCharts and using render_chart_html function from the helper class.
# Demonstrates the ease of generating charts using FusionCharts.
# All the views related to this controller will use the "common" layout.
# As per Ruby On Rails conventions, we have the corresponding views with the same name as the function name in the controller.

class Fusioncharts::BasicExampleController < ApplicationController
  #This is the layout which all functions in this controller make use of.
  layout "common"
  
  #In this action, a pre-defined Data.xml (contained in /Data/ folder) 
  #is used to provide the xml in the dataURL method. 
  #render_chart_html function from the helper is invoked to render the chart.
  #The function itself has no code, all the work is done in the builder and the view.
  def basic_chart
    
  end
    
  #This action demonstrates the ease of generating charts using FusionCharts.
	#Here, we've used a Builder Template to build the XML data.
	#Ideally, you would generate XML data documents at run-time, after interfacing with
	#forms or databases etc. Such examples are also present.
	#Here, we've kept this example very simple.
  #render_chart_html function from the helper is invoked to render the chart.
  #The function itself has no code, all the work is done in the builder and the view.
  def basic_data_xml
    headers["content-type"]="text/html";
  end
  
  #Here, we've used a pre-defined Data.xml (contained in /Data/ folder)
  #Ideally, you would NOT use a physical data file. Instead you'll have 
	#your own code virtually relay the XML data document. Such examples are also present.
  #For a head-start, we've kept this example very simple. 
  #This function uses the dataURL method of FusionCharts. 
  #A view with the same name simple_chart.html.erb is present 
  #and it is this view, which gets shown along with the layout "common".
  #render_chart function from the helper is invoked to render the chart.
  #The function itself has no code, all the work is done in the builder and the view.
  def simple_chart
      
  end
    
  #A Builder Template is used to build the XML data which is hard-coded.
	#Ideally, you would generate XML data documents in the builder at run-time, 
	#after interfacing with forms or databases etc. Such examples are also present.
  #We set the content-type header to text/html. 
  #render_chart function from the helper is invoked to render the chart.
  #The function itself has no code, all the work is done in the builder and the view.  
  def data_xml
    headers["content-type"]="text/html";
  end
    
  #A Builder Template is used to build the XML data which is hard-coded.
  #Ideally, you would generate XML data documents in the builder at run-time, 
  #after interfacing with forms or databases etc. Such examples are also present.
  #We set the content-type header to text/html. 
  #render_chart function from the helper is invoked to render the chart.
  #The function itself has no code, all the work is done in the builder and the view.  
  def multi_chart
    headers["content-type"]="text/html";
  end  
  
end
