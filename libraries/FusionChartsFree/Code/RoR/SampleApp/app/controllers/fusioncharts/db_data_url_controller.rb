# This controller class shows how to render a pie-chart by retrieving 
# factory name and total output quantity dynamically at run-time,
# from the database. setDataURL method is used here.
# As per Ruby On Rails conventions, we have the corresponding views with the same name 
# as the function name in the controller.
class Fusioncharts::DbDataUrlController < ApplicationController
  
 
	#NOTE: It's necessary to encode the dataURL if you've added parameters to it.
  #In this example, we show how to connect FusionCharts to a database 
	#using dataURL method. In our other examples, we've used dataXML method
	#where the XML is generated in the same page as chart. Here, the XML data
	#for the chart would be generated in pie_data function.
  #pie_data action would handle the request and then generate the 
	#XML accordingly.
  def default
    # Escape the URL using CGI.escape if it has parameters 
    @str_data_url = "/Fusioncharts/db_data_url/pie_data"
    
    #The common layout for this view
    render(:layout => "layouts/common")
  end
  # Generates the xml with each factory's name and total output quantity.
  # Content-type for its view is text/xml
  def pie_data
    
      headers["content-type"]="text/xml";
      @factory_data = []
      
      # Find all the factories
      factory_masters = Fusioncharts::FactoryMaster.find(:all)
     
      # For each factory, find the factory output details.
        factory_masters.each do |factory_master|
          factory_name = factory_master.name  
          total = 0.0
          factory_master.factory_output_quantities.each do |factory_output|
            # Total the output quantity for a particular factory
            total = total + factory_output.quantity
          end
          # Append the array of factory name and total output quantity to the existing array @factory_data
          @factory_data<<[factory_name,total]
      end
  end
     
end
