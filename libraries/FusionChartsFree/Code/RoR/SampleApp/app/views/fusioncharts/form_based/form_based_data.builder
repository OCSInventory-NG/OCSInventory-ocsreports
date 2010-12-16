# Builds xml for sales of various product categories 
# of a Restaurant to be shown in form of a pie-chart.
# The values required here are got as parameters
# Expected parameters: str_soups,str_salads,str_sandwiches,str_beverages,str_desserts
xml = Builder::XmlMarkup.new
xml.graph(:caption=>'Sales by Product Category', :subCaption=>'For this week', :showPercentageInLabel=>'1',:pieSliceDepth=>'25',:decimalPrecision=>'0',:showNames=>'1') do
  xml.set(:name=>'Soups',:value=>str_soups) 
  xml.set(:name=>'Salads',:value=>str_salads) 
  xml.set(:name=>'Sandwiches',:value=>str_sandwiches)
  xml.set(:name=>'Beverages',:value=>str_beverages)
  xml.set(:name=>'Desserts',:value=>str_desserts)
end