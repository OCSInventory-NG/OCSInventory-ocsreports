#Model class to store output data of factories.
#As per Ruby On Rails conventions, we have the corresponding table 
#factory_output_quantities in the database.
class Fusioncharts::FactoryOutputQuantity < ActiveRecord::Base
  belongs_to :factory_master
end
