# This controller class will show ways of generating chart by
# * using sales data of products with their names present in an array. 
# * using sales data of products for current year and previous year with their names present in an array.
# * using sales figure and quantity sold in each quarter of a year of a product present in an array.
# * using sales information of two products in each quarter of a year present in an array .
# All the views related to this controller will use the "common" layout.
# As per Ruby On Rails conventions, we have the corresponding views with the same name as the function name in the controller.
class Fusioncharts::ArrayExampleController < ApplicationController
  #This is the layout which all functions in the controller make use of.
  layout "common"
  
  #In this function, we plot a single series chart from data contained
  #in an array. Each element in the array will have two values - first one for data label
  #and the next one for data value.
  #The sales data and product names for 6 products are stored in the array.
  #These values in the array will be used by the builder to build an appropriate xml, 
  #which is then rendered by the corresponding view. 
  def single_series
     headers["content-type"]="text/html"
     @arr_data = []
     @arr_data << ['Product A','567500']
     @arr_data << ['Product B','815300']
     @arr_data << ['Product C','556800']
     @arr_data << ['Product D','734500']
     @arr_data << ['Product E','676800']
     @arr_data << ['Product F','648500']
  end
  
  #In this function, we plot a multi-series chart from data contained
	#in an array. Each element in the array will have three values - first one for data label (product)
	#and the next one store sales information
	#for current year and the last one stores sales information for previous year.
  #The sales data and product names for 6 products are thus, stored. 
  #These values in the array will be used by the builder to build an appropriate xml, 
  #which is then rendered by the corresponding view. 
  def multi_series
      headers["content-type"]="text/html"
      @arr_data = []
      @arr_data << ['Product A','567500','547300']
      @arr_data << ['Product B','815300','584500']
      @arr_data << ['Product C','556800','754000']
      @arr_data << ['Product D','734500','456300']
      @arr_data << ['Product E','676800','754500']
      @arr_data << ['Product F','648500','437600']
  end
    
   #In this function, we plot a Combination chart from data contained
	 #in an array. Each element in the array will have three values - first one for Quarter Name
	 #second one for sales figure and third one for quantity.
   #These values in the array will be used by the builder to build an appropriate xml, 
   #which is then rendered by the corresponding view.    
   def combination
      headers["content-type"]="text/html";
      @arr_data = []
      @arr_data << ['Quarter 1','576000','576']
      @arr_data << ['Quarter 2','448000','448']
      @arr_data << ['Quarter 3','956000','956']
      @arr_data << ['Quarter 4','734000','734']
   end
    
   #In this function, we plot a Stacked chart from data contained
   #in an array. Each element in the array will have three values - first one for Quarter Name
	 #and the next one for sales information
	 #of Product A and the last one for sales information of Product B. 
   def stacked
      headers["content-type"]="text/html";
      @arr_data = []
      @arr_data << ['Quarter 1','567500','547300']
      @arr_data << ['Quarter 2','815300','594500']
      @arr_data << ['Quarter 3','556800','754000']
      @arr_data << ['Quarter 4','734500','456300']
    end
    
end