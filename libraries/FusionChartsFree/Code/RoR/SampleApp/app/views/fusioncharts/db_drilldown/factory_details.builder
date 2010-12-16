#Creates xml with values for date of production and quantity for a particular factory
#The values required for building the xml is obtained as parameter factory_data
#It expects an array in which each element as 
#a hash with values for "date_of_production" and "quantity_number"
#The colors for the columns are obtained by using get_FC_color function from fusioncharts_helper 
xml = Builder::XmlMarkup.new
xml.graph(:palette=>'2', :caption=>'Factory' + factory_id.to_s + ' Output ', :subcaption=>'(In Units)', :xAxisName=>'Date', :showValues=>'1', :decimalPrecision=>'0') do
	for item in factory_data
		xml.set(:name=>item[:date_of_production],:value=>item[:quantity_number],:color=>''+get_FC_color)
	end
end
