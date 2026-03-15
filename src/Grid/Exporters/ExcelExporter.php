<?php

namespace Appsolutely\AIO\Grid\Exporters;

use Appsolutely\AIO\Grid;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\ODS\Writer as OdsWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class ExcelExporter extends AbstractExporter
{
    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $filename = $this->getFilename() . '.' . $this->extension;

        $writer = $this->createWriter();
        $writer->openToBrowser($filename);

        $titles = $this->titles();
        if ($titles) {
            $writer->addRow(Row::fromValues(array_values($titles)));
        }

        if ($this->scope === Grid\Exporter::SCOPE_ALL) {
            $page = 1;
            while (true) {
                $data = $this->buildData($page);
                if (empty($data)) {
                    break;
                }
                $this->writeData($writer, $data, $titles);
                $page++;
            }
        } else {
            $data = $this->buildData();
            if (! empty($data)) {
                $this->writeData($writer, $data, $titles);
            }
        }

        $writer->close();

        exit;
    }

    protected function writeData($writer, array $data, $titles): void
    {
        $keys = $titles ? array_keys($titles) : null;

        foreach ($data as $row) {
            if ($keys) {
                $values = [];
                foreach ($keys as $key) {
                    $values[] = $row[$key] ?? '';
                }
                $writer->addRow(Row::fromValues($values));
            } else {
                $writer->addRow(Row::fromValues(array_values($row)));
            }
        }
    }

    protected function createWriter(): XlsxWriter|CsvWriter|OdsWriter
    {
        return match ($this->extension) {
            'csv'   => new CsvWriter(),
            'ods'   => new OdsWriter(),
            default => new XlsxWriter(),
        };
    }
}
