  # Contains actions to show the UTF-8 chart examples.
  #To use FusionCharts with UTF-8 characters, remember the following:
  #* dataURL method has to be used to get the xml.
  #* Rotated text cannot render UTF-8 characters. For example, UTF-8 characters in the rotated labels will not be rendered correctly.
  #* BOM has to present in the xml given as input to the chart.
  #Steps to ensure correct UTF-8 output:
  #* <meta http-equiv="content-type" content="text/html; charset=utf-8" />	has to be present in the <head> section of the page which contains UTF8 characters. Notice that we have included this in the "common.html.erb" layout, so that it is avialable to all pages.
  #* Put this, headers["Content-Type"] = "text/xml; charset=utf-8"  in the action which is the XML provider. ( Here, pie_data_french or pie_data_japanese actions)
  #* If you are using a xml file for the data, then the file should be saved with UTF-8 encoding with UTF-8 BOM.  It should have the xml declaration also:  <?xml version="1.0" encoding="UTF-8" ?>
  #* If you are generating xml dynamically, then in the xml provider ( here, pie_data_japanese.html.erb and pie_data_french.html.erb ), use get_UTF8_BOM and assign the BOM to the xml as initial value. This function present in fusioncharts_helper library.
  #* After placing the BOM in the xml string, append the xml declaration: <?xml version='1.0' encoding='UTF-8'?>. Finally, append the actual chart xml.
  #* For using data from database, the default character set of the database should be UTF-8. This can be done using the sql command: 
  #ALTER DATABASE DEFAULT CHARACTER SET = utf8; 
  # The default charset of the table containing the UTF characters should be UTF-8. This can be done by adding "DEFAULT CHARSET=utf8;" at the end of the table definition.
  # In the config/database.yml file the setting for encoding should be: encoding: UTF8 
  # For more details on UTF specific code, please see the view where the code resides.
class Fusioncharts::Utf8ExampleController < ApplicationController

  $KCODE = 'u'
  #This is an example showing Japanese characters on the chart.
  #Here, we've used a pre-defined JapaneseData.xml (contained in /Data/ folder)
  #This action uses the dataURL method of FusionCharts. 
  #A view with the same name japanese_xmlfile_example.html.erb is present 
  #and it is this view, which gets rendered with the layout "common".
  #render_chart function from the helper is invoked to render the chart.
  #The function itself has no code, all the work is done in the view.
  def japanese_xmlfile_example
    #The common layout for this view
    render(:layout => "layouts/common")
  end
  
  #In this example, we show how to use UTF8 characters in FusionCharts by connecting to  a database 
  #and using dataURL method. Here, the XML data
  #for the chart is generated in pie_data_japanese function.
  #The function itself does not contain any specific code to handle UTF8 characters.
  #NOTE: It's necessary to encode the dataURL if you've added parameters to it.
  def japanese_dbexample
    # Escape the URL using CGI.escape if it has parameters 
    @str_data_url = "/Fusioncharts/utf8_example/pie_data_japanese"
    
    #The common layout for this view
    render(:layout => "layouts/common")
  end
  
  # Generates the xml with each factory's name and total output quantity.
  # Factory name in japanese is obtained from JapaneseFactoryMaster.
  # Content-type for its view is set to text/xml and char-set to UTF-8.
  def pie_data_japanese
    
      headers["Content-Type"] = "text/xml; charset=utf-8"  # xml content with charset=uttf-8

      @factory_data = []
      
      # Find all the factories
      factory_masters = Fusioncharts::JapaneseFactoryMaster.find(:all)
     
      # For each factory, find the factory output details.
        factory_masters.each do |factory_master|
          factory_name = factory_master.name  
          total = 0.0
          factory_master.factory_output_quantities.each do |factory_output|
            # Total the output quantity for a particular factory
            total = total + factory_output.quantity
          end
          # Append the array of factory name and total output quantity to the existing array @factory_data
          @factory_data<<[factory_name,total]
      end
    end
      
  #This is an example showing French characters on the chart.
  #Here, we've used a pre-defined FrenchData.xml (contained in /Data/ folder)
  #This action uses the dataURL method of FusionCharts. 
  #A view with the same name french_xmlfile_example.html.erb is present 
  #and it is this view, which gets rendered with the layout "common".
  #render_chart function from the helper is invoked to render the chart.
  #The function itself has no code, all the work is done in the view.
  # For more details on UTF specific code, please see the view where the code resides.
  def french_xmlfile_example
    #The common layout for this view
    render(:layout => "layouts/common")
  end
  
  #In this example, we show how to use UTF8 characters in FusionCharts by connecting to  a database 
  #and using dataURL method. Here, the XML data
  #for the chart is generated in pie_data_french function.
  #The function itself does not contain any specific code to handle UTF8 characters.
  #NOTE: It's necessary to encode the dataURL if you've added parameters to it.
  def french_dbexample
    # Escape the URL using CGI.escape if it has parameters 
    @str_data_url = "/Fusioncharts/utf8_example/pie_data_french"
    
    #The common layout for this view
    render(:layout => "layouts/common")
  end
  
  # Generates the xml with each factory's name and total output quantity.
  # Factory name in french is obtained from FrenchFactoryMaster.
  # Content-type for its view is set to text/xml and char-set to UTF-8.
  def pie_data_french
    
      headers["Content-Type"] = "text/xml; charset=utf-8"  # xml content

      @factory_data = []
      
      # Find all the factories
      factory_masters = Fusioncharts::FrenchFactoryMaster.find(:all)
     
      # For each factory, find the factory output details.
        factory_masters.each do |factory_master|
          factory_name = factory_master.name  
          total = 0.0
          factory_master.factory_output_quantities.each do |factory_output|
            # Total the output quantity for a particular factory
            total = total + factory_output.quantity
          end
          # Append the array of factory name and total output quantity to the existing array @factory_data
          @factory_data<<[factory_name,total]
      end
  end
end
