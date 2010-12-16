#Creates xml with values for factory output along with their names. It also creates
#a link to javascript function updateChart
#The values required for building the xml is obtained as parameter factory_data
#It expects an array in which each element as 
#a hash with values for "factory_name","factory_output" and "factory_index"
xml = Builder::XmlMarkup.new
xml.graph(:caption=>'Factory Output report', :subCaption=>'By Quantity',:decimalPrecision=>'0' ,:showNames=>'1' ,:numberSuffix=>' Units' ,:pieSliceDepth=>'20' ,:formatNumberScale=>'0' ) do
	for item in factory_data
		xml.set(:name=>item[:factory_name],:value=>item[:factory_output],:link=>'javaScript:updateChart('+item[:factory_index].to_s+ ',"'+item[:factory_name]+'");' )
	end
end