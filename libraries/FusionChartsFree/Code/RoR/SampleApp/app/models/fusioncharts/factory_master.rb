#Model class to store data of factory id and name.
#As per Ruby On Rails conventions, we have the corresponding table 
#factory_masters in the database.
class Fusioncharts::FactoryMaster < ActiveRecord::Base
  has_many :factory_output_quantities,
                :order => 'date_pro asc'
end
