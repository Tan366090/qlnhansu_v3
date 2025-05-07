import PDFDocument from "pdfkit";

export const generatePDF = async (data, month, year) => {
    return new Promise((resolve, reject) => {
        try {
            const doc = new PDFDocument();
            const chunks = [];

            doc.on("data", chunk => chunks.push(chunk));
            doc.on("end", () => resolve(Buffer.concat(chunks)));
            doc.on("error", reject);

            // Add title
            doc.fontSize(20).text("Payroll Report", { align: "center" });
            doc.moveDown();
            doc.fontSize(16).text(`${month}/${year}`, { align: "center" });
            doc.moveDown(2);

            // Add table headers
            const tableHeaders = ["Employee", "Department", "Basic Salary", "Allowances", "Deductions", "Net Salary"];
            const columnWidths = [150, 100, 100, 100, 100, 100];
            let x = 50;
            
            doc.font("Helvetica-Bold");
            tableHeaders.forEach((header, i) => {
                doc.text(header, x, doc.y, { width: columnWidths[i] });
                x += columnWidths[i];
            });
            doc.moveDown();

            // Add table rows
            doc.font("Helvetica");
            data.forEach(row => {
                x = 50;
                const values = [
                    row.employee_name,
                    row.department,
                    row.basic_salary.toLocaleString(),
                    row.allowances.toLocaleString(),
                    row.deductions.toLocaleString(),
                    row.net_salary.toLocaleString()
                ];

                values.forEach((value, i) => {
                    doc.text(value, x, doc.y, { width: columnWidths[i] });
                    x += columnWidths[i];
                });
                doc.moveDown();
            });

            doc.end();
        } catch (error) {
            reject(error);
        }
    });
}; 