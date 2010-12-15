#Creates xml with values for sales data of products 
#for the current year and the previous year.
#The values required for building the xml is obtained as parameter arr_data
#It expects an array in which each element is 
#itself an array with first element as label, second element as current year sales value
#and third element as previous year value
xml = Builder::XmlMarkup.new
xml.graph(:caption=>'Sales by Product', :numberPrefix=>'$', :formatNumberScale=>'1', :rotateValues=>'1', :placeValuesInside=>'1',:decimalPrecision=>'0') do
  # Iterate through the array to create the <category> tags within <categories>
	xml.categories do
		for item in arr_data
			xml.category(:name=>item[0]) 
		end
  end
  # Iterate through the array to create the <set> tags within dataset for series 'Current Year'
	xml.dataset(:seriesName=>'Current Year',:color=>'AFD8F8') do
		for item in arr_data
			xml.set(:value=>item[1])
		end
  end
  # Iterate through the array to create the <set> tags within dataset for series 'Previous Year'
	xml.dataset(:seriesName=>'Previous Year',:color=>'F6BD0F') do
		for item in arr_data
			xml.set(:value=>item[2])
		end
	end
end