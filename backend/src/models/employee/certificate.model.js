const { DataTypes } = require('sequelize');
const sequelize = require('../../config/database');

const Certificate = sequelize.define('Certificate', {
    id: {
        type: DataTypes.INTEGER,
        primaryKey: true,
        autoIncrement: true
    },
    employee_id: {
        type: DataTypes.INTEGER,
        allowNull: false,
        references: {
            model: 'employees',
            key: 'id'
        }
    },
    certificate_type: {
        type: DataTypes.STRING(100),
        allowNull: false
    },
    issue_date: {
        type: DataTypes.DATE,
        allowNull: false
    },
    expiry_date: {
        type: DataTypes.DATE,
        allowNull: true
    },
    issuing_organization: {
        type: DataTypes.STRING(200),
        allowNull: false
    },
    certificate_number: {
        type: DataTypes.STRING(50),
        allowNull: true
    },
    file_path: {
        type: DataTypes.STRING(255),
        allowNull: true
    },
    file_name: {
        type: DataTypes.STRING(255),
        allowNull: true
    },
    file_size: {
        type: DataTypes.INTEGER,
        allowNull: true
    },
    file_type: {
        type: DataTypes.STRING(100),
        allowNull: true
    },
    status: {
        type: DataTypes.ENUM('active', 'expired', 'revoked'),
        allowNull: false,
        defaultValue: 'active'
    },
    notes: {
        type: DataTypes.TEXT,
        allowNull: true
    }
}, {
    tableName: 'certificates',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at'
});

module.exports = Certificate; 