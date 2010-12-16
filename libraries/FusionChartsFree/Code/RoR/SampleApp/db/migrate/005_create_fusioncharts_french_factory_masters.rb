class CreateFusionchartsFrenchFactoryMasters < ActiveRecord::Migration
  def self.up
    create_table :fusioncharts_french_factory_masters do |t|

      t.timestamps
    end
  end

  def self.down
    drop_table :fusioncharts_french_factory_masters
  end
end
