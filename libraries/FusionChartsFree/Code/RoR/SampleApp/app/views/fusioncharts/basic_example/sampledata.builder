#Creates xml with values for monthly sales data 
#The values required for building the xml are hard-coded in this file
xml = Builder::XmlMarkup.new
xml.graph(:caption=>'Monthly Unit Sales', :xAxisName=>'Month', :yAxisName=>'Units', :decimalPrecision=>'0', :formatNumberScale=>'0') do
  xml.set(:name=>'Jan',:value=>'462',:color=>'AFD8F8') 
  xml.set(:name=>'Feb',:value=>'857',:color=>'F6BD0F') 
  xml.set(:name=>'Mar',:value=>'671',:color=>'8BBA00')
  xml.set(:name=>'Apr',:value=>'494',:color=>'FF8E46')
  xml.set(:name=>'May',:value=>'761',:color=>'008E8E')
  xml.set(:name=>'Jun',:value=>'960',:color=>'D64646')
  xml.set(:name=>'Jul',:value=>'629',:color=>'8E468E') 
  xml.set(:name=>'Aug',:value=>'622',:color=>'588526')
  xml.set(:name=>'Sep',:value=>'376',:color=>'B3AA00')
  xml.set(:name=>'Oct',:value=>'494',:color=>'008ED6')
  xml.set(:name=>'Nov',:value=>'761',:color=>'9D080D')
  xml.set(:name=>'Dec',:value=>'960',:color=>'A186BE')
end