# This controller demonstrates the generation of pie-chart 
# by using the values from a form.
# Only the chart.html.erb view related to the chart action uses the "common" layout.
# Here, we have given a simple example, where the form fields do not belong to any database
# In real-world, you would have fields corresponding to the fields of some Model. 
class Fusioncharts::FormBasedController < ApplicationController
  
  #This action will present a form to the user, to input data.
  #For this demo, we present a very simple form intended for a Restaurant to indicate
  #sales of its various product categories at lunch time (for a week). 
  #The form is rendered using the page default.html.erb. It submits its data to
  #chart action. 
  #So, basically the view for this action is just a form. 
  def default
    headers["content-type"]="text/html";
  end

  #Data is obtained from the submitted form (present in the request)
  #In this example, we're directly showing this data back on chart.
	#In your apps, you can do the required processing and then show the 
	#relevant data only.
  #The view for this action uses the "common" layout.
  def chart
    headers["content-type"]="text/html";
    # Get the values from the request using params[...]
    @str_soups = params[:Soups]
    @str_salads = params[:Salads]
    @str_sandwiches = params[:Sandwiches]
    @str_beverages = params[:Beverages]
    @str_desserts = params[:Desserts]
    
    #The common layout is used only by this function in controller.
    render(:layout => "layouts/common")
  end
    
end
