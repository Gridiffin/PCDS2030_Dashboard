/**
 * Sector-specific data schemas for the PCDS2030 Dashboard
 * Each sector has its own set of metrics with appropriate data types
 */
const sectorSchemas = {
  "forestry": {
    "name": "Forestry",
    "metrics": [
      {
        "id": "timberExport",
        "label": "Timber Export Value",
        "type": "currency",
        "unit": "USD",
        "description": "Total value of timber exports for the reporting period"
      },
      {
        "id": "reforestationArea", 
        "label": "Reforested Area",
        "type": "number",
        "unit": "hectares",
        "description": "Total area reforested during the reporting period"
      },
      {
        "id": "endangeredSpecies",
        "label": "Endangered Species Count",
        "type": "number",
        "unit": "species",
        "description": "Number of endangered species in protected forestry areas"
      }
    ],
    "tables": [
      {
        "id": "timberByRegion",
        "label": "Timber Production by Region",
        "columns": [
          {"id": "region", "label": "Region", "type": "text"},
          {"id": "volume", "label": "Volume", "type": "number", "unit": "cubic meters"},
          {"id": "value", "label": "Value", "type": "currency", "unit": "USD"}
        ],
        "allowAddRows": true
      }
    ]
  },
  "land": {
    "name": "Land Management",
    "metrics": [
      {
        "id": "landAllocated",
        "label": "Land Allocated for Development",
        "type": "number",
        "unit": "hectares",
        "description": "Total land area allocated for development projects"
      },
      {
        "id": "permitIssued",
        "label": "Development Permits Issued",
        "type": "number",
        "unit": "permits",
        "description": "Number of development permits issued during reporting period"
      }
    ],
    "tables": [
      {
        "id": "landUseBreakdown",
        "label": "Land Use Breakdown",
        "columns": [
          {"id": "category", "label": "Land Use Category", "type": "text"},
          {"id": "area", "label": "Area", "type": "number", "unit": "hectares"},
          {"id": "percentage", "label": "Percentage", "type": "percentage"}
        ],
        "allowAddRows": true
      }
    ]
  }
  // Additional sectors can be added here
};

/**
 * This is how the data would be stored in the database JSON column
 */
const exampleStoredData = {
  // Common fields remain the same
  "programName": "Forest Conservation Program",
  "year": "2024",
  "quarter": "Q1",
  
  // Add sector metrics section for the specific metrics
  "sectorData": {
    // Simple metrics (similar to Excel cells)
    "metrics": {
      "timberExport": 500000,
      "reforestationArea": 1200,
      "endangeredSpecies": 25
    },
    
    // Tabular data (similar to Excel tables)
    "tables": {
      "timberByRegion": [
        {"region": "North", "volume": 5000, "value": 200000},
        {"region": "South", "volume": 3000, "value": 150000},
        {"region": "East", "volume": 4000, "value": 180000}
      ]
    }
  },
  
  // Status remains the same
  "status": {
    "date": "2024-05-15",
    "notes": "Project progressing as planned",
    "challenges": "Delays in South region due to heavy rainfall"
  }
};

// Export the schemas for use in forms and validation
export { sectorSchemas };
