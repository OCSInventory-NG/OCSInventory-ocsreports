#Creates xml with values for sales data of products 
#and the quantity of sales in each quarter of a year
#by a combination of differing scales of two y-axes.
#The values required for building the xml is obtained as parameter arr_data
#It expects an array in which each element is 
#itself an array with first element as label, second element as sales value
#and third element as quantity value
xml = Builder::XmlMarkup.new
xml.graph(:palette=>'4', :caption=>'Product A - Sales Details', :PYAxisName=>'Revenue', :SYAxisName=>'Quantity (in Units)', :numberPrefix=>'$', :formatNumberScale=>'0', :showValues=>'0',:decimalPrecision=>'0',:anchorSides=>'10',:anchorRadius=>'3',:anchorBorderColor=>'FF8000') do
	# Run a loop to create the <category> tags within <categories>
  xml.categories do
		for item in arr_data
			xml.category(:name=>item[0])
		end
	end
	# Run a loop to create the <set> tags within dataset for series 'Revenue'
  xml.dataset(:seriesName=>'Revenue',:color=>'AFD8F8') do
		for item in arr_data
			xml.set(:value=>item[1])
		end
	end
	# Run a loop to create the <set> tags within dataset for series 'Quantity'
  # Set the :parentYAxis attribute to secondary, ie., 'S'
  xml.dataset(:seriesName=>'Quantity', :parentYAxis=>'S',:color=>'FF8000') do
		for item in arr_data
			xml.set(:value=>item[2])
		end
	end
end