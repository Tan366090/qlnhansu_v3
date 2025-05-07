const { DataTypes } = require('sequelize');
const sequelize = require('../../config/database');

const CourseMaterial = sequelize.define('CourseMaterial', {
    id: {
        type: DataTypes.INTEGER,
        primaryKey: true,
        autoIncrement: true
    },
    course_id: {
        type: DataTypes.INTEGER,
        allowNull: false,
        references: {
            model: 'courses',
            key: 'id'
        }
    },
    file_name: {
        type: DataTypes.STRING(255),
        allowNull: false
    },
    file_path: {
        type: DataTypes.STRING(255),
        allowNull: false
    },
    file_size: {
        type: DataTypes.INTEGER,
        allowNull: false
    },
    file_type: {
        type: DataTypes.STRING(100),
        allowNull: false
    }
}, {
    tableName: 'course_materials',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at'
});

module.exports = CourseMaterial; 