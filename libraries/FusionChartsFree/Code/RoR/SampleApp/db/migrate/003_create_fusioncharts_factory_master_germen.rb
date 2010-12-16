class CreateFusionchartsFactoryMasterGermen < ActiveRecord::Migration
  def self.up
    create_table :fusioncharts_factory_master_germen do |t|

      t.timestamps
    end
  end

  def self.down
    drop_table :fusioncharts_factory_master_germen
  end
end
