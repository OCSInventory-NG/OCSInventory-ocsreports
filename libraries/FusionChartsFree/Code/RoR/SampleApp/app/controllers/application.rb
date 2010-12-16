# Filters added to this controller apply to all controllers in the application.
# Likewise, all the methods added will be available for all controllers.

class ApplicationController < ActionController::Base
  helper :all # include all helpers, all the time

  # See ActionController::RequestForgeryProtection for details
  # Uncomment the :secret if you're not using the cookie session store
  protect_from_forgery # :secret => 'e7ce10757c59806280c459a85d20c43b'
   
   # Formats the date to dd/mm without leading zeroes
  def format_date_remove_zeroes(date_to_format)  
        date_num= date_to_format.strftime('%d').to_i
        month_num = date_to_format.strftime('%m').to_i
        formatted_date=date_num.to_s+"/"+month_num.to_s         
  end
end
