#There are two examples in this controller.
#*Pie-chart for total ouput quantities of each factory by getting data from database and using dataXML method
#*Pie-chart for total ouput quantities of each factory and a link to another chart 
#which gives detailed information for selected factory
#All the views related to this controller will use the "common" layout.
#As per Ruby On Rails conventions, we have the corresponding views 
#with the same name as the function name in the controller.
class Fusioncharts::DbExampleController < ApplicationController
  #This is the layout which all functions in this controller make use of.
  layout "common"
  
  #This action retrieves the values from the database and constructs an array 
  #to hold, factory name and corresponding total output quantity.
  #The view for this action basic_dbexample will use the array values to construct the
  #xml for this chart. To build the xml, the view takes help from the builder file (basic_factories_quantity.builder)
  def basic_dbexample
      headers["content-type"]="text/html";
      @factory_data = [] 
      #Get data from factory masters table
      
      factory_masters = Fusioncharts::FactoryMaster.find(:all)
      factory_masters.each do |factory_master| 
          total = 0.0
          factory_id = factory_master.id
          factory_name = factory_master.name
          factory_master.factory_output_quantities.each do |factory_output|
                  total = total + factory_output.quantity
          end
          # Push the hash of values into the array             
          @factory_data<<{:factory_name=>factory_name,:factory_output=>total}
      end    
  end
    
end
