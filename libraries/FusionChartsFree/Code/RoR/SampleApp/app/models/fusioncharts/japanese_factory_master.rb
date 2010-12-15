class Fusioncharts::JapaneseFactoryMaster < ActiveRecord::Base
  has_many :factory_output_quantities,
                :order => 'date_pro asc', 
                :foreign_key=>"factory_master_id"
end
