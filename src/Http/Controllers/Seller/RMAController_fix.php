        } catch (\Exception $e) {
            // Log error pero continuar
        }

        $statusText = $data['rma_status'] == self::ACCEPT ? 'aceptada' : 'rechazada';
        session()->flash('success', "RMA {$statusText} exitosamente");

        return redirect()->route('goloba.seller.rma.view', $data['rma_id']);
    }
}
