module XmlHelper
  # This function helps you escape special characters in XML attribute values
  # Here, we have escaped only single quotes for xml attribute values
  # You can escape other characters which might be causing issues as xml attribute values
  def XmlHelper.escape_xml_attribute_values(string_to_escape,for_data_url)
    escaped_str= string_to_escape
    # In case of inline xml, we need to escape the single quotes
    if for_data_url==false
      escaped_str = escaped_str.gsub(%r{'},'%26apos;');
    end
    # Common Replacements
    # In Ruby On Rails, the builder automatically escapes < , >, %
    # Hence these replacements are not required
    
    #We've not considered any special characters here. 
    #You can add them as per your language and requirements.
    return escaped_str
  end
   # This function escapes the double quotes
  def XmlHelper.escape_double_quotes(str_to_escape)
    # Hash containing the required conversion
    conversions = {
       %r{"}=>'&quot;'
    }  
    escaped_str = str_to_escape
    conversions.each do |x,y|
      escaped_str = escaped_str.gsub(x,y)
    end  
    return escaped_str
  end
  # This function escapes the single quotes
  def XmlHelper.escape_single_quotes(str_to_escape)
    # Hash containing the required conversion
    conversions = {
       %r{'}=>'%26apos;'
    }  
    escaped_str = str_to_escape
    conversions.each do |x,y|
      escaped_str = escaped_str.gsub(x,y)
    end  
    return escaped_str
  end
   
   #The builder also encloses attribute values in double quotes. We will replace them with single quotes.
  def XmlHelper.escape_builder_xml(xml_to_escape)
    # Hash containing all the required conversions
      conversions = {
      %r{"}=>'\''
     }  
      escaped_xml = xml_to_escape
      conversions.each do |x,y|
          escaped_xml = escaped_xml.gsub(x,y)
        end  
        return escaped_xml
    end
end
