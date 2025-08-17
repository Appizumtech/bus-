export const createBus = async (req: Request, res: Response) => {
    try {
        const bus = new Bus(req.body);
        await bus.save();
        res.status(201).json(bus);
    } catch (error) {
        res.status(400).json({ message: 'Error creating bus' });
    }
};

export const updateBus = async (req: Request, res: Response) => {
    try {
        const bus = await Bus.findByIdAndUpdate(req.params.id, req.body, { new: true });
        res.json(bus);
    } catch (error) {
        res.status(400).json({ message: 'Error updating bus' });
    }
}; 