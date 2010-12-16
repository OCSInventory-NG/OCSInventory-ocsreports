#Creates xml with values for sales data of products 
#along with their names.
#The values required for building the xml is obtained as parameter arr_data
#It expects an array in which each element is 
#itself an array with first element as label and second element as value
xml = Builder::XmlMarkup.new
xml.graph(:caption=>'Sales by Product', :numberPrefix=>'$', :formatNumberScale=>'0',:decimalPrecision=>'0') do
	for item in arr_data
		xml.set(:name=>item[0], :value=>item[1],:color=>''+get_FC_color)
	end
end