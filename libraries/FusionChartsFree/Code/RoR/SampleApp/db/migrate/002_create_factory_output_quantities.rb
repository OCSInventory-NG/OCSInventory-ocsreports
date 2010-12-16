class CreateFactoryOutputQuantities < ActiveRecord::Migration
  def self.up
    create_table :factory_output_quantities do |t|
      t.primary_key :id
      t.integer :factory_master_id
      t.datetime :date_pro
      t.integer :quantity
    end
  end

  def self.down
    drop_table :factory_output_quantities
  end
end
