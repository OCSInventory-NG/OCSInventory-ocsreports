# In this controller, we will plot a pie-chart showing total output of quantities 
# and name of each factory in a pie-section.
# On clicking on a pie-section, we obtain detailed information on the quantity 
# produced and date of production of the factory by using javascript.
class Fusioncharts::DbJsController < ApplicationController
  
  #In this action, the total quantity produced and name of each factory 
  #is obtained from the database and plotted.
  #On clicking on a pie-section, it links to another chart giving detailed information 
  #of each output produced and the date of production. For this dataURL method is used from the js.
  #of that factory. An array is created to store the index, factory name and total output. This 
  #array is accessible to the view. Also, a js_var_string variable is constructed.
  #This variable contains javascript code to create an array, to contain the date of production
  #and the factory output quantity.
  def default
    headers["content-type"]="text/html";
    @factory_data = [] 
    @js_var_string =""
    index_count = -1
    #Get data from factory masters table
    factory_masters = Fusioncharts::FactoryMaster.find(:all)
      factory_masters.each do |factory_master| 
        total=0.0
        index_count = index_count + 1
        factory_id = factory_master.id
        factory_name = factory_master.name
        # Construct the javascript variable to hold an array.
        @js_var_string =@js_var_string+ "data[" + index_count.to_s + "] = new Array();\n" ; 

        factory_master.factory_output_quantities.each do |factory_output|
                      date_of_production = factory_output.date_pro
                      # Formats the date to dd/mm without leading zeroes
                      formatted_date = format_date_remove_zeroes(date_of_production)
                      quantity_number = factory_output.quantity
                      # Calculate the total quantity for this factory
                      total = total + factory_output.quantity
                      # Append values to the javascript array
                      @js_var_string =@js_var_string+ "\t\t\t\tdata[" + index_count.to_s + "].push(new Array('" + formatted_date + "','" +quantity_number.to_s+"'));\n" 
                    end
        #Formatting the output html
        @js_var_string =@js_var_string+"\t\t\t";
        #Push hash of values into the array          
        @factory_data<<{:factory_index=>index_count,:factory_name=>factory_name,:factory_output=>total}
      end
  end
    
end
